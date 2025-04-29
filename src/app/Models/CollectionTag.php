<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionTag extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    const GRADE_5 = 1;
    const GRADE_6 = 2;
    const GRADE_7 = 3;
    const GRADE_8 = 4;
    const GRADE_9 = 5;
    const GRADE_10 = 6;
    const GRADE_11 = 7;
    const GRADE_12 = 8;
    const EVENTS = 9;
    const TEST_PREP = 10;
    const ROOTS_AND_AFFIXES = 11;
    const LITERATURE = 12;
    const JUST_FOR_FUN = 13;
    const NON_FICTION = 14;

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }
}
