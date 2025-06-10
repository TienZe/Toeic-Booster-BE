<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ToeicChatHistory extends Model
{
    protected $guarded = [];

    public function toeicTestAttempt(): BelongsTo
    {
        return $this->belongsTo(ToeicTestAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(ToeicChatContent::class)->orderBy('created_at', 'asc');
    }

    public function displayContents()
    {
        return $this->contents()->where('hidden', 0);
    }
}
