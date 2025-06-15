<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToeicChatHistory\CreateToeicChatHistoryRequest;
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

    public function getChatHistory(Request $request, $attemptId, $questionId)
    {
        $chatHistory = $this->toeicChatService->getChatHistory($attemptId, $questionId, $request->all());

        return $chatHistory;
    }

    public function createNewChatHistory(CreateToeicChatHistoryRequest $request)
    {
        $validated = $request->validated();

        if (isset($validated['question_number'])) {
            $chatHistory = $this->toeicChatService->createChatHistoryByQuestionNumber($validated['attempt_id'], $validated['question_number']);
        } else {
            $chatHistory = $this->toeicChatService->createChatHistory($validated['attempt_id'], $validated['question_id']);
        }

        return $chatHistory;
    }
}
