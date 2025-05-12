<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonVocabulary extends Model
{
    protected $table = 'lesson_vocabularies';
    protected $guarded = [];

    public $timestamps = false;


    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function vocabulary()
    {
        return $this->belongsTo(Vocabulary::class);
    }

    public function lessonLearnings()
    {
        return $this->hasMany(LessonLearning::class);
    }
}
