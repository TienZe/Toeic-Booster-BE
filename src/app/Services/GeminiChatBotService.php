<?php

namespace App\Services;

use App\Enums\MediaFileType;
use App\Models\Question;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Enums\MimeType;
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
        $prompt .= "\n\nCâu hỏi số $question->question_number - Part $partNumber: $question->question";

        $prompt .= "\nA. " . $question->A;
        $prompt .= "\nB. " . $question->B;
        $prompt .= "\nC. " . $question->C;

        if ($partNumber != '2') {
            $prompt .= "\nD. " . $question->D;
        }

        $prompt .= "\n\nĐáp án đúng là: " . $question->correct_answer;

        $prompt .= "\n\nGiải thích: " . $question->explanation;

        if ($questionGroup->transcript) {
            $prompt .= "\n\nTranscript: " . $questionGroup->transcript;
        }

        if ($questionGroup->passage) {
            $prompt .= "\n\nĐoạn văn: " . $questionGroup->passage;
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
}