<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToeicTestAttempt\GetAttemptDetailsRequest;
use App\Http\Requests\ToeicTestAttempt\GetAttemptsRequest;
use App\Http\Requests\ToeicTestAttempt\SaveToeicAttemptRequest;
use App\Services\ToeicTestAttemptService;
use Illuminate\Http\Request;

class ToeicTestAttemptController extends Controller
{
    private $toeicTestAttemptService;

    public function __construct(ToeicTestAttemptService $toeicTestAttemptService)
    {
        $this->toeicTestAttemptService = $toeicTestAttemptService;
    }

    public function getAttempts(GetAttemptsRequest $request)
    {
        $result = $this->toeicTestAttemptService->getAttempts($request->user()->id, $request->validated());
        return $result;
    }

    public function store(SaveToeicAttemptRequest $request)
    {
        $result = $this->toeicTestAttemptService->saveAttempt($request->validated(), $request->user()->id);
        return $result;
    }

    public function getAttemptDetails($attemptId, GetAttemptDetailsRequest $request)
    {
        $result = $this->toeicTestAttemptService->getAttemptDetails($attemptId, $request->validated());
        return $result;
    }

    public function getAttemptStatsOfUser(Request $request)
    {
        $result = $this->toeicTestAttemptService->getAttemptStatsOfUser($request->user()->id, $request->recent_days ?? 7);
        return $result;
    }
}
