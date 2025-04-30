<?php

namespace App\Http\Controllers;

use App\Models\CollectionTag;
use Illuminate\Http\Request;

class CollectionTagController extends Controller
{
    public function index()
    {
        return CollectionTag::all();
    }
}
