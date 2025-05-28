<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionGroup extends Model
{
    protected $guarded = [];

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('question_number');
    }

    public function medias()
    {
        return $this->hasMany(QuestionMedia::class);
    }
}
