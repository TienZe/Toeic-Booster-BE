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

        $systemInstruction = "Các câu trả lời cần dựa vào ngữ cảnh của câu hỏi TOEIC được cung cấp bên dưới:";

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

        if ($question->transcript) {
            $prompt .= "\n\nTranscript: " . $question->transcript;
        }

        if ($question->passage) {
            $prompt .= "\n\nĐoạn văn: " . $question->passage;
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
                "Bạn là một gia sư AI thân thiện và chuyên nghiệp, chuyên luyện thi TOEIC 2 kỹ năng (Listening & Reading). Hãy trả lời ngắn gọn, dễ hiểu, đúng trọng tâm."
            ))
            ->generateContent(...$contents);

        $parts = $result->parts();

        return new Content($parts, Role::MODEL);
    }
}