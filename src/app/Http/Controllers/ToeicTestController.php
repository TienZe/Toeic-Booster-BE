<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToeicTest\SaveToeicTestRequest;
use Illuminate\Http\Response;

class ToeicTestController extends Controller
{
    protected $toeicTestService;

    public function __construct(ToeicTestService $toeicTestService)
    {
        $this->toeicTestService = $toeicTestService;
    }

    // Update or create toeic test
    public function save(SaveToeicTestRequest $request)
    {
        $this->toeicTestService->save($request->validated());$this->toeicTestService->save($request->validated());$this->toeicTestService->save($request->validated());$this->toeicTestService->save($request->validated());$this->toeicTestService->save($request->validated());
    }
}