<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToeicTest\GetToeicTestsRequest;
use App\Http\Requests\ToeicTest\SaveToeicTestRequest;
use App\Services\ToeicTestService;
use Illuminate\Http\Request;
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
        $savedToeicTest = $this->toeicTestService->saveToeicTest($request->validated());

        return $savedToeicTest;
    }

    public function index(GetToeicTestsRequest $request)
    {
        return $this->toeicTestService->getListOfToeicTests($request->validated());
    }

    public function show($id)
    {
        return $this->toeicTestService->getToeicTestById($id);
    }

    public function getToeicTestInfo($id)
    {
        return $this->toeicTestService->getToeicTestInfo($id);
    }

    public function destroy($id)
    {
        $deleted = $this->toeicTestService->deleteToeicTest($id);

        return [ "deleted" => $deleted ];
    }

    public function getMostTakenToeicTests(Request $request)
    {
        return $this->toeicTestService->getMostTakenToeicTests($request->limit ?? 8);
    }
}