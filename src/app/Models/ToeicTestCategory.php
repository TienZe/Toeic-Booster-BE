<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToeicTestCategory extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    const CATEGORY_2024 = 1;
    const CATEGORY_2023 = 2;
    const CATEGORY_2022 = 3;
    const CATEGORY_2021 = 4;
    const CATEGORY_2020 = 5;
    const CATEGORY_2019 = 6;
    const CATEGORY_NEW_ECONOMY = 7;
}
