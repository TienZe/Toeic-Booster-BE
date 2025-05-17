<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionRating extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'collection_id',
        'user_id',
        'rate',
        'personal_message',
    ];

    /**
     * Get the collection that owns the rating.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the user that owns the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
