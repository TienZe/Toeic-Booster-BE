<?php

namespace App\Services;

use App\Entities\GeneratedWord;
use App\Enums\MediaFileType;
use App\Enums\PartOfSpeech;
use App\Helpers\MarkdownHelper;
use App\Models\Question;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Part;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;

class GeminiChatBotService
{
    public static function createContent(string $text, $imageUrl = null): Content
    {
        $parts = [$text];

        if ($imageUrl) {
            $parts[] = new Blob(
                mimeType: MimeType::IMAGE_JPEG,
                data: base64_encode(file_get_contents($imageUrl))
            );
        }

        $content = Content::parse($parts);

        return $content;
    }

    public static function createInstructionFromQuestion($questionId)
    {
        $question = Question::with('questionGroup')->find($questionId);

        $questionGroup = $question->questionGroup;
        $partNumber = substr($questionGroup->part, -1);

        $systemInstruction = "I will provide a TOEIC question.
All your responses must be strictly based on the context of that specific TOEIC question or TOEIC field.
Do not provide any information outside of that context, even if I ask.";

        $prompt = $systemInstruction;
        $prompt .= "\n\nQuestion number $question->question_number - Part $partNumber: $question->question";

        $prompt .= "\nA. " . $question->A;
        $prompt .= "\nB. " . $question->B;
        $prompt .= "\nC. " . $question->C;

        if ($partNumber != '2') {
            $prompt .= "\nD. " . $question->D;
        }

        $prompt .= "\n\nThe correct answer is: " . $question->correct_answer;

        $prompt .= "\n\nExplanation: " . $question->explanation;

        if ($questionGroup->transcript) {
            $prompt .= "\n\nTranscript: " . $questionGroup->transcript;
        }

        if ($questionGroup->passage) {
            $prompt .= "\n\nPassage: " . $questionGroup->passage;
        }

        $images = $questionGroup->medias->filter(function ($media) {
            return $media->file_type == MediaFileType::IMAGE->value;
        });

        return Content::parse([
            $prompt,
            ...$images->map(function ($img) {
                $mimeType = MimeType::IMAGE_JPEG;

                if (strpos($img->file_url, '.png') !== false) {
                    $mimeType = MimeType::IMAGE_PNG;
                }

                return new Blob(
                    mimeType: $mimeType,
                    data: base64_encode(file_get_contents($img->file_url))
                );
            }),
        ]);
    }

    public static function generateContent(array $contents)
    {
        $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->withSystemInstruction(Content::parse(
                <<<PROMPT

All rules are strict and must be followed.

1. You are a friendly and professional AI tutor, specialized in preparing students for the TOEIC Listening and Reading test.
- Always respond concisely, clearly, and to the point. Always respond in Vietnamese except when you are asked to provide a TOEIC practice question.
- If the question is unrelated to TOEIC and the chat history, respond with: 'Xin lỗi, tôi không thuộc lĩnh vực mà bạn đang đề cập'.
- If asked about model information, respond with: 'Tôi được huấn luyện bởi Toeic Booster.'

2. For a text-only response (not JSON), format the message using Markdown for clear presentation (e.g., using lists, bolding, table, etc.).

3. The ONLY time you are allowed to respond with JSON is when you are generating an 'option' type response as defined in Rule 4. For ALL other types of requests (like providing vocabulary, explanations, or lists of information), you MUST respond with plain text, formatted using Markdown (as per Rule 2). Do NOT use JSON for lists.

4. You MUST use the JSON 'option' type for the following two cases.
    (1) ANY time you generate a multiple-choice question (e.g., when the user asks to "generate a question", "create a quiz", or for a "similar question"):
        - The question in the `text` field MUST be a valid TOEIC-style question and MUST be in English.
        - The `options` array MUST contain all the answer choices.
        - Each option string MUST start with 'A.', 'B.', 'C.', or 'D.' followed by a space and then the option text. For example: "A. on time", "B. in time". Do not deviate from this format.
    (2) When you need to ask a clarifying question because the user's request is ambiguous.

    The JSON object MUST be in the following format:
    {
        "text": "<message to the user or the question>",
        "options": [
            "<button label>",
            "...",
            "<button label>"
        ],
        "type": "option"
    }
PROMPT
            ))
            ->generateContent(...$contents);

        $parts = $result->parts(); # $candidates[0]->content->parts

        $preprocessingParts = array_map(function ($part) {
            // remove markdown ```json
            $newText = MarkdownHelper::cleanJsonMarkdown($part->text);
            return new Part(
                $newText,
                $part->inlineData,
                $part->fileData,
                $part->functionCall,
                $part->functionResponse,
                $part->executableCode,
                $part->codeExecutionResult
            );
        }, $parts);

        return new Content($preprocessingParts, Role::MODEL);
    }

    public static function generateStructuredOutput($schemaProperties, $prompt)
    {
        $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: new Schema(
                        type: DataType::OBJECT,
                        properties: $schemaProperties,
                    )
                )
            )
            ->generateContent($prompt);

        return $result->json(); // json_decode($candidates[0].parts[0].text)
    }

    public static function generateWord(GeneratedWord $baseWord): GeneratedWord
    {
        $schemaProperties = [
            'word' => new Schema(type: DataType::STRING, example: 'hello'),
            'definition' => new Schema(type: DataType::STRING, example: 'an expression of greeting'),
            'meaning' => new Schema(type: DataType::STRING, example: 'xin chào', description: 'Meaning in Vietnamese, short and concise'),
            'pronunciation' => new Schema(type: DataType::STRING, example: '/həˈloʊ/'),
            'example' => new Schema(type: DataType::STRING, example: 'Hello, how are you?', description: 'Example in English. About 10 - 15 words.'),
            'exampleMeaning' => new Schema(type: DataType::STRING, example: 'Xin chào, bạn có khoẻ không?', description: 'Meaning of the example in Vietnamese'),
            'partOfSpeech' => new Schema(type: DataType::STRING, enum: PartOfSpeech::values(), example: 'noun', description: 'Part of speech of the word'),
        ];

        $prompt = "You are EN - VI dictionary. Just fill in the missing information for the word in English except `meaning` and `exampleMeaning`: " . json_encode($baseWord);

        $result = self::generateStructuredOutput($schemaProperties, $prompt);

        $generatedWord = new GeneratedWord();
        $generatedWord->fromArray((array) $result);

        return $generatedWord;
    }
}
