<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToeicTest extends Model
{
    protected $guarded = [];
    protected $with = ['category'];

    public function questionGroups()
    {
        return $this->hasMany(QuestionGroup::class);
    }

    public function category()
    {
        return $this->belongsTo(ToeicTestCategory::class, 'toeic_test_category_id');
    }
}
