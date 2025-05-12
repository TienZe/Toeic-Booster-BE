<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonExamAnswer extends Model
{
    protected $fillable = [
        'lesson_exam_id',
        'lesson_vocabulary_id',
        'is_correct',
    ];

    public function lessonExam(): BelongsTo
    {
        return $this->belongsTo(LessonExam::class);
    }

    public function lessonVocabulary(): BelongsTo
    {
        return $this->belongsTo(LessonVocabulary::class);
    }
}
