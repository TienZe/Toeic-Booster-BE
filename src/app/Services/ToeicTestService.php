<?php

namespace App\Services;

use App\Models\ToeicTest;
use App\Models\QuestionGroup;
use App\Models\Question;
use App\Models\QuestionMedia;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;

class ToeicTestService
{
    public function saveToeicTest(array $data): void
    {
        DB::transaction(function () use ($data) {
            // Save or update ToeicTest
            $toeicTest = (isset($data['id']) && $data['id']) ? ToeicTest::find($data['id']) : new ToeicTest();
            if (!$toeicTest) {
                $toeicTest = new ToeicTest();
            }
            $toeicTest->fill([
                'name' => $data['name'] ?? $toeicTest->name,
                'description' => $data['description'] ?? $toeicTest->description,
            ]);
            $toeicTest->save();

            // Save or update QuestionGroups
            if (!empty($data['question_groups'])) {
                foreach ($data['question_groups'] as $groupData) {
                    $questionGroup = (isset($groupData['id']) && $groupData['id']) ? QuestionGroup::find($groupData['id']) : new QuestionGroup();
                    if (!$questionGroup) {
                        $questionGroup = new QuestionGroup();
                    }
                    $questionGroup->fill([
                        'part' => $groupData['part'] ?? $questionGroup->part,
                        'transcript' => $groupData['transcript'] ?? $questionGroup->transcript,
                        'toeic_test_id' => $toeicTest->id,
                    ]);
                    $questionGroup->save();

                    // Save or update Questions
                    if (!empty($groupData['questions'])) {
                        foreach ($groupData['questions'] as $questionData) {
                            $question = (isset($questionData['id']) && $questionData['id']) ? Question::find($questionData['id']) : new Question();
                            if (!$question) {
                                $question = new Question();
                            }
                            $question->fill([
                                'question' => $questionData['question'] ?? $question->question,
                                'question_number' => $questionData['questionNumber'] ?? $question->question_number,
                                'explanation' => $questionData['explanation'] ?? $question->explanation,
                                'A' => $questionData['A'] ?? $question->A,
                                'B' => $questionData['B'] ?? $question->B,
                                'C' => $questionData['C'] ?? $question->C,
                                'D' => $questionData['D'] ?? $question->D,
                                'correct_answer' => $questionData['correctAnswer'] ?? $question->correct_answer,
                                'question_group_id' => $questionGroup->id,
                            ]);
                            $question->save();
                        }
                    }

                    // Save or update Medias
                    if (!empty($groupData['medias'])) {
                        foreach ($groupData['medias'] as $mediaData) {
                            $media = (isset($mediaData['id']) && $mediaData['id']) ? QuestionMedia::find($mediaData['id']) : new QuestionMedia();
                            if (!$media) {
                                $media = new QuestionMedia();
                            }
                            // Handle Cloudinary upload if fileUrl is base64, otherwise just save the URL
                            $fileUrl = $mediaData['fileUrl'] ?? null;
                            $isBase64 = $fileUrl && !str_starts_with($fileUrl, 'http');

                            if ($isBase64) {
                                // Delete old file if updating
                                if ($media->file_public_id) {
                                    Cloudinary::uploadApi()->destroy($media->file_public_id);
                                }
                                $folder = $mediaData['fileType'] === 'audio' ? 'question_medias/audio' : 'question_medias/image';
                                $uploadResult = Cloudinary::uploadApi()->upload($fileUrl, [
                                    'folder' => $folder,
                                    'resource_type' => 'auto',
                                ]);
                                $media->file_url = $uploadResult['secure_url'];
                                $media->file_public_id = $uploadResult['public_id'];
                            } elseif ($fileUrl) {
                                $media->file_url = $fileUrl;
                                // Do not change file_public_id if just using an existing URL
                            }
                            $media->fill([
                                'file_type' => $mediaData['fileType'] ?? $media->file_type,
                                'order' => $mediaData['order'] ?? $media->order,
                                'question_group_id' => $questionGroup->id,
                            ]);
                            $media->save();
                        }
                    }
                }
            }
        });
    }

    public function getToeicTest(int $id)
    {
        return ToeicTest::with('questionGroups.questions', 'questionGroups.medias')->find($id);
    }
}