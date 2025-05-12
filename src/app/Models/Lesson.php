<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    const THUMBNAIL_FOLDER = 'lesson_thumbnails';


    const LEARNING_STEP_FILTERED = "filtered";
    const LEARNING_STEP_EXAMINED = "examined";

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    protected $appends = [ 'num_of_words' ];

    /**
     * Get the collection that owns the lesson.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function lessonVocabularies()
    {
        return $this->hasMany(LessonVocabulary::class);
    }

    public function getNumOfWordsAttribute(): int
    {
        return $this->lessonVocabularies()->count();
    }

    public function lessonLearnings()
    {
        return $this->hasMany(LessonLearning::class);
    }

    public function lessonExams()
    {
        return $this->hasMany(LessonExam::class);
    }
}
