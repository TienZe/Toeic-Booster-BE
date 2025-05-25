<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToeicTestAttempt extends Model
{
    protected $guarded = [];

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }

    public function toeicTest()
    {
        return $this->belongsTo(ToeicTest::class);
    }
}
