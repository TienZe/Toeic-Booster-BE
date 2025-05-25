<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\ToeicTestAttempt;
use App\Models\UserAnswer;

class ToeicTestAttemptService
{
    public function saveAttempt(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $attempt = ToeicTestAttempt::create([
                'user_id' => $userId,
                'toeic_test_id' => $data['toeic_test_id'],
                'taken_time' => $data['taken_time'] ?? null,
                'selected_parts' => $data['selected_parts'] ?? null,
            ]);

            $answers = [];
            foreach ($data['user_answers'] as $answer) {
                $answers[] = UserAnswer::create([
                    'user_id' => $userId,
                    'toeic_test_attempt_id' => $attempt->id,
                    'question_id' => $answer['question_id'],
                    'choice' => $answer['choice'],
                ]);
            }

            return $attempt->refresh()->load('userAnswers');
        });
    }
}
