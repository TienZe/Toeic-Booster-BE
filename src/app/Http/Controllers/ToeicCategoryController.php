<?php

namespace App\Http\Controllers;

use App\Models\ToeicTestCategory;

class ToeicCategoryController extends Controller
{
    public function index()
    {
        return ToeicTestCategory::all();
    }
}
