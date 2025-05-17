<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
