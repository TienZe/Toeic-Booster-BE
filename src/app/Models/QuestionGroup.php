<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionGroup extends Model
{
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function medias()
    {
        return $this->hasMany(QuestionMedia::class);
    }
}
