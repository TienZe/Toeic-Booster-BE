<?php

namespace App\Services;

use App\Enums\ToeicPart;
use App\Helpers\ToeicHelper;
use App\Models\Question;
use App\Models\ToeicTest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\ToeicTestAttempt;
use App\Models\UserAnswer;

class ToeicTestAttemptService
{
    protected $toeicTestService;

    public function __construct(ToeicTestService $toeicTestService)
    {
        $this->toeicTestService = $toeicTestService;
    }

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

    public function getAttemptDetails($attemptId, $options = [])
    {
        $attempt = ToeicTestAttempt::find($attemptId);

        $toeicTest = ToeicTest::with(['questionGroups' => function ($query) use ($attempt) {
            $query->whereIn('part', $attempt->selected_parts);
            $query->orderBy('group_index');
        }, 'questionGroups.questions.userAnswers' => function ($query) use ($attempt) {
            $query->where('toeic_test_attempt_id', $attempt->id);
        }, 'questionGroups.questions.userAnswers.question.questionGroup', // load inverse relation from userAnswers to question and to questionGroup
        'questionGroups.medias'])->where('id', $attempt->toeic_test_id)->first();

        foreach ($toeicTest->questionGroups as $questionGroup) {
            foreach ($questionGroup->questions as $question) {
                $question->user_answer = $question->userAnswers->first();
                unset($question->userAnswers);
            }
        }

        $attempt->toeic_test = $toeicTest;

        if (isset($options['with_result_summary'])) {
            // 1. Number of correct and incorrect questions
            $questionOfSelectedParts = $toeicTest->questionGroups->pluck('questions')->flatten();
            $userAnswers = $questionOfSelectedParts->pluck('user_answer')->filter(); // only include record for answered questions

            $attempt->append(['num_of_incorrect_answers', 'num_of_correct_answers', 'total_questions']);

            // 2. Number of correct questions of each skills
            $correctUserAnswers = $userAnswers->filter(function ($userAnswer) {
                return $userAnswer->is_correct;
            });

            $correctRCAnswers = $correctUserAnswers->filter(function ($userAnswer) {
                $part = $userAnswer->question->questionGroup->part;
                return ToeicPart::isReading($part);
            });

            $correctLCAnswers = $correctUserAnswers->filter(function ($userAnswer) {
                $part = $userAnswer->question->questionGroup->part;
                return ToeicPart::isListening($part);
            });

            $attempt->num_correct_lc_questions = $correctLCAnswers->count();
            $attempt->num_correct_rc_questions = $correctRCAnswers->count();
        }

        return $attempt;
    }

    public function getAccuracyByDateOfAttempts($attempts)
    {
        $attemptsByDate = $attempts->groupBy(function ($attempt) {
            return $attempt->created_at->format('Y-m-d');
        });

        $accuracyByDate = collect();

        foreach ($attemptsByDate as $date => $attempts) {
            $totalCorrectAnswers = $attempts->sum(function ($attempt) {
                return $attempt->num_of_correct_answers;
            });

            $totalAnsweredQuestions = $attempts->sum(function ($attempt) {
                return $attempt->userAnswers->count();
            });

            $accuracyByDate->push([
                "date" => $date,
                "accuracy" => round($totalCorrectAnswers / $totalAnsweredQuestions * 100, 2)
            ]);
        }

        $sortedAccuracyByDate = $accuracyByDate->sortBy('date'); // no need to convert to date object because date is in format lexicographically Y-m-d

        return $sortedAccuracyByDate->map(function ($item) {
            $item['date'] = Carbon::parse($item['date'])->format('d/m/Y');
            return $item;
        });
    }

    public function getNumOfCorrectAnswersGroupedByPart($attempts)
    {
        $allUserAnswers = $attempts->pluck('userAnswers')->flatten();

        $answersByPart = $allUserAnswers->groupBy('question.questionGroup.part')->sortKeys();

        $numCorrectAnswerByPart = [];

        foreach ($answersByPart as $part => $answers) {
            $numCorrect = $answers->filter(function ($userAnswer) {
                return $userAnswer->is_correct;
            })->count();

            $total = $answers->count();

            $numCorrectAnswerByPart[$part] = [
                'numCorrect' => $numCorrect,
                'total' => $total,
            ];
        }

        return $numCorrectAnswerByPart;
    }

    public function getAttempts($userId, $options = [])
    {
        $limit = $options['limit'] ?? 10;
        $offset = $options['offset'] ?? 0;

        $attempts = ToeicTestAttempt::with(['toeicTest', 'userAnswers'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset);

        if (isset($options['recent_days'])) {
            $attempts->where('created_at', '>=', now()->subDays($options['recent_days']));
        }

        if (isset($options['toeic_test_id'])) {
            $attempts->where('toeic_test_id', $options['toeic_test_id']);
        }

        $attempts = $attempts->get();

        $attempts->each(function ($attempt) {
            $attempt->append(['num_of_correct_answers', 'total_questions']);
        });

        return $attempts;
    }


    public function getAttemptStatsOfUser($userId, $recentDays = 7)
    {
        $attempts = ToeicTestAttempt::with('userAnswers.question.questionGroup', 'userAnswers.attempt')->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($recentDays))
            ->get();

        $lcMaxScore = $attempts->max('listening_score');
        $rcMaxScore = $attempts->max('reading_score');

        $numberOfPracticeTests = $attempts->pluck('toeic_test_id')->unique()->count();
        $practiceTime = ceil($attempts->sum('taken_time') / 60); // in minutes

        // Split by skills, only account the attempt that have the user answer in the respective parts of each skill
        $allUserAnswers = $attempts->pluck('userAnswers')->flatten();

        $lcAnswers = $allUserAnswers->filter(function ($userAnswer) {
            $part = $userAnswer->question->questionGroup->part;
            return ToeicPart::isListening($part);
        });

        $rcAnswers = $allUserAnswers->filter(function ($userAnswer) {
            $part = $userAnswer->question->questionGroup->part;
            return ToeicPart::isReading($part);
        });

        $lcAttempts = $lcAnswers->pluck('attempt')->unique('id');
        $lcAverageTime = round($lcAttempts->avg('taken_time')); // in seconds

        $lcAnswersByAttempt = $lcAnswers->groupBy('toeic_test_attempt_id');
        $lcNumOfCorrects = $lcAnswersByAttempt->map(function ($answersOfAttempt) {
            $numCorrect = $answersOfAttempt->filter(function ($userAnswer) {
                return $userAnswer->is_correct;
            })->count();

            return $numCorrect;
        })->sum();

        $averageNumOfCorrectLc = (int)round($lcNumOfCorrects / $lcAnswersByAttempt->count());
        $averageLcScore = ToeicHelper::LISTENING_SCORE_MAP[$averageNumOfCorrectLc] ?? 0;

        $rcAttempts = $rcAnswers->pluck('attempt')->unique('id');
        $rcAverageTime = round($rcAttempts->avg('taken_time')); // in seconds

        $rcAnswersByAttempt = $rcAnswers->groupBy('toeic_test_attempt_id');
        $rcNumOfCorrects = $rcAnswersByAttempt->map(function ($answersOfAttempt) {
            $numCorrect = $answersOfAttempt->filter(function ($userAnswer) {
                return $userAnswer->is_correct;
            })->count();

            return $numCorrect;
        })->sum();

        $averageNumOfCorrectRc = (int)round($rcNumOfCorrects / $rcAnswersByAttempt->count());
        $averageRcScore = ToeicHelper::READING_SCORE_MAP[$averageNumOfCorrectRc] ?? 0;

        $lcPracticeTests = $lcAnswers->pluck('attempt.toeic_test_id')->unique()->count();
        $rcPracticeTests = $rcAnswers->pluck('attempt.toeic_test_id')->unique()->count();

        $numOfLcAnswers = $lcAnswers->count();
        $numOfRcAnswers = $rcAnswers->count();

        $numOfCorrectLcAnswers = $lcAnswers->filter(function ($userAnswer) {
            return $userAnswer->is_correct;
        })->count();

        $numOfCorrectRcAnswers = $rcAnswers->filter(function ($userAnswer) {
            return $userAnswer->is_correct;
        })->count();

        $lcAccuracyByDate = $this->getAccuracyByDateOfAttempts($lcAttempts);
        $rcAccuracyByDate = $this->getAccuracyByDateOfAttempts($rcAttempts);

        return [
            'numberOfPracticeTests' => $numberOfPracticeTests,
            'practiceTime' => $practiceTime,
            'lc' => [
                'practiceTests' => $lcPracticeTests,
                'answers' => $numOfLcAnswers,
                'correctAnswers' => $numOfCorrectLcAnswers,
                'maxScore' => $lcMaxScore,
                'averageScore' => $averageLcScore,
                'averageTime' => $lcAverageTime,
                'accuracyByDate' => $lcAccuracyByDate,
            ],
            'rc' => [
                'practiceTests' => $rcPracticeTests,
                'answers' => $numOfRcAnswers,
                'correctAnswers' => $numOfCorrectRcAnswers,
                'maxScore' => $rcMaxScore,
                'averageScore' => $averageRcScore,
                'averageTime' => $rcAverageTime,
                'accuracyByDate' => $rcAccuracyByDate,
            ],
            'numOfCorrectAnswersGroupedByPart' => $this->getNumOfCorrectAnswersGroupedByPart($attempts),
        ];
    }
}