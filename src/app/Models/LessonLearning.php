<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonLearning extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    // Learner
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Lesson vocabularies that the learner has learned
    public function lessonVocabulary()
    {
        return $this->belongsTo(LessonVocabulary::class);
    }

    // Lessons that the learner has learned
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
