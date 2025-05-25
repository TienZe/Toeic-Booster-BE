<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToeicTestAttempt\SaveToeicAttemptRequest;
use App\Services\ToeicTestAttemptService;

class ToeicTestAttemptController extends Controller
{
    public function store(SaveToeicAttemptRequest $request, ToeicTestAttemptService $service)
    {
        $result = $service->saveAttempt($request->validated(), $request->user()->id);
        return $result;
    }
}
