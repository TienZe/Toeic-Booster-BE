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
}
