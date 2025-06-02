<?php

namespace App\Services;

use App\Entities\GeneratedWord;
use App\Enums\MediaFileType;
use App\Enums\PartOfSpeech;
use App\Models\Question;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
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
        $parts = [ $text ];

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
All your responses must be strictly based on the context of that specific TOEIC question.
Do not provide any information outside of that context, even if I ask.
Keep your answers concise.";

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

                return  new Blob(
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
"You are a friendly and professional AI tutor, specialized in preparing students for the TOEIC Listening and Reading test.
Always respond concisely, clearly, and to the point. Always respond in Vietnamese.
If the question is unrelated to TOEIC, respond with: 'Xin lỗi, tôi không thuộc lĩnh vực mà bạn đang đề cập'.
If asked about model information, respond with: 'Tôi được huấn luyện bởi Toeic Booster.'"))
            ->generateContent(...$contents);

        $parts = $result->parts();

        return new Content($parts, Role::MODEL);
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

        return $result->json();
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
        $generatedWord->fromArray((array)$result);

        return $generatedWord;
    }
}