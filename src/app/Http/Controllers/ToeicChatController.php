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
        $toeicChatHistoryId = $request->input('toeic_chat_history_id');
        $contextQuestionNumber = $request->input('context_question_number');
        $userText = $request->input('text');

        $responseText = $this->toeicChatService->processAndResponseAssistantMessageFromQuestion($toeicChatHistoryId, $userText, $contextQuestionNumber);

        return ['text' => $responseText];
    }

    public function getChatHistory($attemptId, $questionId)
    {
        $chatHistory = $this->toeicChatService->getChatHistory($attemptId, $questionId);

        return response()->json($chatHistory);
    }
}
