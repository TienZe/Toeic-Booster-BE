<?php

namespace App\Services;

use App\Enums\ToeicPart;
use App\Helpers\ToeicHelper;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use App\Models\ToeicTestAttempt;
use App\Models\UserAnswer;

class ToeicTestAttemptService
{
    public function saveAttempt(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $attemptData =[
                'user_id' => $userId,
                'toeic_test_id' => $data['toeic_test_id'],
                'taken_time' => $data['taken_time'] ?? null,
                'selected_parts' => $data['selected_parts'] ?? null,
            ];

            // Calculate the score
            $questions = Question::with('questionGroup')
                ->whereIn('id', array_column($data['user_answers'], 'question_id'))
                ->get();

            $lcCorrectQuestions = 0;
            $rcCorrectQuestions = 0;
            $questionId2UserChoice = collect($data['user_answers'])->pluck('choice', 'question_id')->toArray();

            foreach ($questions as $question) {
                $part = $question->questionGroup->part;
                $correctAnswer = $question->correct_answer;
                $userChoice = $questionId2UserChoice[$question->id];

                if ($userChoice === $correctAnswer) {
                    if (ToeicPart::isListening($part)) {
                        $lcCorrectQuestions++;
                    } else {
                        $rcCorrectQuestions++;
                    }
                }
            }

            $lcScore = ToeicHelper::LISTENING_SCORE_MAP[$lcCorrectQuestions] ?? 0;
            $rcScore = ToeicHelper::READING_SCORE_MAP[$rcCorrectQuestions] ?? 0;

            $attemptData['listening_score'] = $lcScore;
            $attemptData['reading_score'] = $rcScore;
            $attemptData['score'] = $lcScore + $rcScore;

            // Create the attempt
            $attempt = ToeicTestAttempt::create($attemptData);

            // Save user answers
            $answers = [];
            foreach ($data['user_answers'] as $answer) {
                $respectiveQuestion = $questions->find($answer['question_id']);

                $answers[] = UserAnswer::create([
                    'user_id' => $userId,
                    'toeic_test_attempt_id' => $attempt->id,
                    'question_id' => $answer['question_id'],
                    'choice' => $answer['choice'],
                    'correct_answer' => $respectiveQuestion->correct_answer,
                ]);
            }

            return $attempt->refresh()->load('userAnswers');
        });
    }

    public function getAttemptsOfUserByToeicTestId($userId, $toeicTestId)
    {
        $attempts = ToeicTestAttempt::with('userAnswers')->where('user_id', $userId)
            ->where('toeic_test_id', $toeicTestId)
            ->orderBy('created_at', 'desc')
            ->get();

        $attempts->each(function ($attempt) {
            // Append number of questions
            $totalQuestions = 0;

            foreach ($attempt->selected_parts as $part) {
                $totalQuestions += ToeicHelper::getNumberOfQuestionsByPart($part);
            }

            $attempt->total_questions = $totalQuestions;

            // Append number of correct questions
            $correctQuestions = $attempt->userAnswers->filter(function ($userAnswer) {
                return $userAnswer->correct_answer === $userAnswer->choice;
            })->count();

            $attempt->number_of_correct_questions = $correctQuestions;
        });

        return $attempts;
    }
}
