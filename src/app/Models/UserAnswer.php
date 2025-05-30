<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $guarded = [];

    protected $appends = ['is_correct'];

    public function getIsCorrectAttribute()
    {
        return $this->correct_answer === $this->choice;
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function attempt()
    {
        return $this->belongsTo(ToeicTestAttempt::class, 'toeic_test_attempt_id');
    }
}
