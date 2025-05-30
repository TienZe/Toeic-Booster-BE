<?php

namespace App\Models;

use App\Enums\ToeicPart;
use App\Helpers\ToeicHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ToeicTestAttempt extends Model
{
    protected $guarded = [];

    protected $appends = ['is_full_test'];

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function toeicTest()
    {
        return $this->belongsTo(ToeicTest::class);
    }

    protected function selectedParts(): Attribute // new way to define accessor and mutator
    {
        return Attribute::make(
            get: fn (mixed $value) => explode(',', $value),
            set: fn (mixed $value) => is_array($value) ? implode(',', $value) : $value,
        );
    }

    protected function getIsFullTestAttribute() // old way to define accessor
    {
        $selectedParts = $this->selected_parts;

        return $selectedParts === ToeicHelper::ALL_PARTS;
    }

    public function getTotalQuestionsAttribute()
    {
        $totalQuestions = 0;

        foreach ($this->selected_parts as $part) {
            $totalQuestions += ToeicHelper::getNumberOfQuestionsByPart($part);
        }

        return $totalQuestions;
    }

    public function getNumOfCorrectAnswersAttribute()
    {
        return $this->userAnswers->filter(function ($userAnswer) {
            return $userAnswer->is_correct;
        })->count();
    }

    public function getNumOfIncorrectAnswersAttribute()
    {
        return $this->userAnswers->filter(function ($userAnswer) {
            return !$userAnswer->is_correct;
        })->count();
    }
}
