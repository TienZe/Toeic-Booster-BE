<?php

namespace App\Http\Controllers;

use App\Services\ToeicChatService;
use Illuminate\Http\Request;

class ToeicChatController extends Controller
{
    private $toeicChatService;

    public function __construct(ToeicChatService $toeicChatService)
    {
        $this->toeicChatService = $toeicChatService;
    }

    public function chat(Request $request)
    {
        $attemptId     = $request->input('toeic_test_attempt_id');
        $questionId    = $request->input('question_id');
        $userText      = $request->input('text');

        $responseText = $this->toeicChatService->processAndResponseAssistantMessageFromQuestion($attemptId, $questionId, $userText);

        return ['text' => $responseText];
    }

    public function getChatHistory($attemptId, $questionId)
    {
        $chatHistory = $this->toeicChatService->getChatHistory($attemptId, $questionId);

        return response()->json($chatHistory);
    }
}
