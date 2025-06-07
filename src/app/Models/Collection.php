<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Collection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    protected $with = ['tags'];

    const THUMBNAIL_FOLDER = 'collection_thumbnails';

    /**
     * Scope to count unique students who have taken lessons from this collection
     * This is more efficient than the accessor-based approach
     */
    public function scopeWithStudentCount($query)
    {
        return $query->withCount([
            'lessons as num_of_taken_students' => function ($query) {
                $query->select(\DB::raw('COUNT(DISTINCT lesson_learnings.user_id)'))
                    ->join('lesson_learnings', 'lessons.id', '=', 'lesson_learnings.lesson_id');
            }
        ]);
    }

    /**
     * Get the lessons for the collection.
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function tags()
    {
        return $this->belongsToMany(CollectionTag::class);
    }

    /**
     * Get the ratings for the collection.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(CollectionRating::class);
    }
}
