<?php

namespace App\Models;

use App\Helpers\BingImageHelper;
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

    // Utility relation to get the first vocabulary of the lesson
    public function firstLessonVocabulary()
    {
        return $this->hasOne(LessonVocabulary::class)->orderBy('id');
    }

    public function lessonLearnings()
    {
        return $this->hasMany(LessonLearning::class);
    }

    public function lessonExams()
    {
        return $this->hasMany(LessonExam::class);
    }

    public function getNumOfWordsAttribute(): int
    {
        return $this->lessonVocabularies()->count();
    }

    public function getReservedThumbnailAttribute()
    {
        // Pls eager load before using this attribute to avoid N+1 query problem
        $lessonVoca = $this->firstLessonVocabulary;
        if (!$lessonVoca) {
            return null;
        }

        if ($lessonVoca->thumbnail) {
            return $lessonVoca->thumbnail;
        }

        $systemWord = $lessonVoca->vocabulary;
        if ($systemWord) {
            if ($systemWord->thumbnail) {
                return $systemWord->thumbnail;
            }

            $keyword = $lessonVoca->word ?? $systemWord->word;

            if ($keyword) {
                return BingImageHelper::getBingImageByKeyword($keyword);
            }
        }

        return null;
    }
}
