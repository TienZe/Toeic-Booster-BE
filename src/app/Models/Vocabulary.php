<?php

namespace App\Models;

use App\Enums\PartOfSpeech;
use Illuminate\Database\Eloquent\Model;

class Vocabulary extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'part_of_speech' => PartOfSpeech::class,
    ];

    /**
     * Cloudinary folder for vocabulary thumbnails
     */
    const THUMBNAIL_FOLDER = 'vocabulary/thumbnails';

    /**
     * Cloudinary folder for pronunciation audio files
     */
    const PRONUNCIATION_AUDIO_FOLDER = 'vocabulary/pronunciation_audios';

    /**
     * Cloudinary folder for example audio files
     */
    const EXAMPLE_AUDIO_FOLDER = 'vocabulary/example_audios';
}
