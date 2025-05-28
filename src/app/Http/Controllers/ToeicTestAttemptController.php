<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToeicTestAttempt\GetAttemptDetailsRequest;
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

    public function store(SaveToeicAttemptRequest $request)
    {
        $result = $this->toeicTestAttemptService->saveAttempt($request->validated(), $request->user()->id);
        return $result;
    }

    public function getAttemptsOfUser(Request $request)
    {
        $result = $this->toeicTestAttemptService->getAttemptsOfUserByToeicTestId($request->user()->id, $request->toeic_test_id);
        return $result;
    }

    public function getAttemptDetails($attemptId, GetAttemptDetailsRequest $request)
    {
        $result = $this->toeicTestAttemptService->getAttemptDetails($attemptId, $request->validated());
        return $result;
    }
}
