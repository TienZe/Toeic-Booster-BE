<?php

namespace App\Services;

use App\Entities\PaginatedList;
use App\Models\ToeicTest;
use App\Models\QuestionGroup;
use App\Models\Question;
use App\Models\QuestionMedia;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;

class ToeicTestService
{
    public function saveToeicTest(array $data): ToeicTest|null
    {
        $savedToeicTest = null;

        DB::transaction(function () use ($data, &$savedToeicTest) {
            // Save or update ToeicTest
            $toeicTest = (isset($data['id']) && $data['id']) ? ToeicTest::find($data['id']) : new ToeicTest();
            if (!$toeicTest) {
                $toeicTest = new ToeicTest();
            }

            $toeicTest->fill([
                'name' => $data['name'] ?? $toeicTest->name,
                'description' => $data['description'] ?? $toeicTest->description,
                'toeic_test_category_id' => $data['category'] ?? $toeicTest->toeic_test_category_id,
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
                        'transcript' => isset($groupData['transcript']) ? $groupData['transcript'] : $questionGroup->transcript,
                        'passage' => isset($groupData['passage']) ? $groupData['passage'] : $questionGroup->passage,
                        'toeic_test_id' => $toeicTest->id,
                        'group_index' => isset($groupData['group_index']) ? $groupData['group_index'] : $questionGroup->group_index,
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
                                'question' => $questionData['question'] ?? $question->question ?? "",
                                'question_number' => $questionData['question_number'] ?? $question->question_number,
                                'explanation' => isset($questionData['explanation']) ? $questionData['explanation'] : $question->explanation,
                                'A' => $questionData['A'] ?? $question->A,
                                'B' => $questionData['B'] ?? $question->B,
                                'C' => $questionData['C'] ?? $question->C,
                                'D' => $questionData['D'] ?? $question->D,
                                'correct_answer' => $questionData['correct_answer'] ?? $question->correct_answer,
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
                            $fileUrl = $mediaData['file_url'] ?? null;
                            $isBase64 = $fileUrl && !str_starts_with($fileUrl, 'http');

                            if ($isBase64) {
                                // Delete old file if updating
                                if ($media->file_public_id) {
                                    Cloudinary::uploadApi()->destroy($media->file_public_id);
                                }
                                $folder = $mediaData['file_type'] === 'audio' ? 'question_medias/audio' : 'question_medias/image';
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
                                'file_type' => $mediaData['file_type'] ?? $media->file_type,
                                'order' => $mediaData['order'] ?? $media->order,
                                'question_group_id' => $questionGroup->id,
                            ]);
                            $media->save();
                        }
                    }
                }
            }

            $savedToeicTest = $toeicTest;
        });

        if (isset($savedToeicTest) && isset($savedToeicTest->id)) {
            return $this->getToeicTestById($savedToeicTest->id);
        }

        return null;
    }

    public function getToeicTestById($id)
    {
        return ToeicTest::with(['questionGroups' => function ($query) {
            $query->orderBy('group_index');
        }, 'questionGroups.questions', 'questionGroups.medias'])->where('id', $id)->first();
    }

    public function getListOfToeicTests(array $options)
    {
        $limit = $options['limit'] ?? 10;
        $page = $options['page'] ?? 0;

        $query = ToeicTest::query();

        if (isset($options['search'])) {
            $query->where('name', 'like', '%' . $options['search'] . '%');
        }

        if (isset($options['filtered_category'])) {
            $query->where('toeic_test_category_id', $options['filtered_category']);
        }

        if (isset($options['with_stats'])) {
            // TODO: add stats: comment count, taken student count,
        }

        $paginatedList = PaginatedList::createFromQueryBuilder($query, $page, $limit);

        return $paginatedList;
    }

    public function getToeicTestInfo($id)
    {
        return ToeicTest::find($id);
    }

    public function deleteToeicTest($id)
    {
        return ToeicTest::find($id)->delete();
    }

    public function getMostTakenToeicTests($limit = 8)
    {
        return ToeicTest::withCount('attempts')
            ->orderBy('attempts_count', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        /** Laravel load the count by using co-related sub-query (not efficient)
         *  SELECT
         *     `toeic_tests`.*, (
         *      SELECT
         *          count(*)
         *      FROM `toeic_test_attempts`
         *      WHERE `toeic_tests`.`id` = `toeic_test_attempts`.`toeic_test_id`
         *  ) AS `attempts_count`
         *  FROM `toeic_tests`
         *  ORDER BY `attempts_count` desc, `id` desc
         *  LIMIT 6;
         */
    }
}