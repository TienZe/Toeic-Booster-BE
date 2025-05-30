<?php

namespace App\Services;

use App\Models\ToeicChatHistory;
use Illuminate\Support\Facades\DB;

class ToeicChatService
{
    public function processAndResponseAssistantMessageFromQuestion($attemptId, $questionId, $userText)
    {
        $responseText = null;

        DB::transaction(function () use ($attemptId, $questionId, $userText, &$responseText) {
            // Get the last chat history
            $chatHistory = ToeicChatHistory::where('toeic_test_attempt_id', $attemptId)
                ->where('question_id', $questionId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$chatHistory) {
                $chatHistory = $this->createChatHistory($attemptId, $questionId);
            }

            // Generate the user content from user text
            $userContent = GeminiChatBotService::createContent($userText);
            $chatHistory->contents()->create([
                'content_serialized' => serialize($userContent),
            ]);

            // Construct the chat history (array of contents)
            $contents = $chatHistory->contents->map(function ($contentModel) {
                return $contentModel->content;
            })->toArray();

            $responseContent = GeminiChatBotService::generateContent($contents);

            // Save the model response content
            $chatHistory->contents()->create([
                'content_serialized' => serialize($responseContent),
            ]);

            $responseText = $responseContent->parts[0]->text;
        });

        return $responseText;
    }

    public function createChatHistory($attemptId, $questionId)
    {
        $chatHistory = ToeicChatHistory::create([
            'toeic_test_attempt_id' => $attemptId,
            'question_id' => $questionId,
        ]);

        // Create the first instruction from question
        $firstInstructionFromQuestion = GeminiChatBotService::createInstructionFromQuestion($questionId);
        $chatHistory->contents()->create([
            'content_serialized' => serialize($firstInstructionFromQuestion),
        ]);

        return $chatHistory;
    }

    public function getChatHistory($attemptId, $questionId)
    {
        $chatHistory = ToeicChatHistory::with('contents')
            ->where('toeic_test_attempt_id', $attemptId)
            ->where('question_id', $questionId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$chatHistory) {
            $chatHistory = $this->createChatHistory($attemptId, $questionId);
        }

        $chatHistory->chatContents = $chatHistory->contents->slice(1);
        unset($chatHistory->contents);

        $chatHistory->chatContents = $chatHistory->chatContents->map(function ($contentModel) {
            $dtoContent = [
                "id" => $contentModel->id,
                "createdAt" => $contentModel->created_at,
            ];

            foreach ($contentModel->content->parts as $part) {
                $dtoContent["parts"][] = [
                    'text' => $part->text,
                    'inlineData' => $part->inlineData,
                ];
            }

            $dtoContent['role'] = $contentModel->content->role;

            return $dtoContent;
        });

        return $chatHistory;
    }
}