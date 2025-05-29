<?php

namespace App\Models;

use Gemini\Data\Content;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ToeicChatContent extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'content_serialized',
    ];

    protected $appends = [
        'content',
    ];

    public function history(): BelongsTo
    {
        return $this->belongsTo(ToeicChatHistory::class, 'toeic_chat_history_id');
    }

    public function getContentAttribute(): Content
    {
        return unserialize($this->content_serialized);
    }
}
