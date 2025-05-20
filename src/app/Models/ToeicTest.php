<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToeicTest extends Model
{
    protected $guarded = [];

    public function questionGroups()
    {
        return $this->hasMany(QuestionGroup::class);
    }
}
