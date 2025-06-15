<?php

namespace App\Services;

use App\Models\ToeicChatHistory;
use App\Models\ToeicTestAttempt;
use Gemini\Data\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ToeicChatService
{
    private $toeicTestService;

    public function __construct(ToeicTestService $toeicTestService)
    {
        $this->toeicTestService = $toeicTestService;
    }

    public function processAndResponseAssistantMessageFromQuestion($chatHistoryId, $userText, $contextQuestionNumber)
    {
        $responseTextOrObject = null;

        DB::transaction(function () use ($chatHistoryId, $contextQuestionNumber, $userText, &$responseTextOrObject) {
            // Get the last chat history
            $chatHistory = ToeicChatHistory::find($chatHistoryId);
            if (!$chatHistory) {
                return;
            }

            if (isset($contextQuestionNumber)) {
                // Append new question instruction for the posted context question
                $chatHistory->load('toeicTestAttempt.toeicTest.questionGroups.questions');
                $questionId = $chatHistory->toeicTestAttempt?->toeicTest
                    ?->questionGroups
                        ?->pluck('questions')->flatten()
                    ->where('question_number', $contextQuestionNumber)
                    ->first()?->id;

                // Append new question instruction if the question is not the last indexed question
                $lastIndexedQuestionId = $chatHistory->indexed_question_id;
                if ($questionId != $lastIndexedQuestionId) {
                    $this->appendQuestionInstructionContent($chatHistory, $questionId);
                }
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

            $responseContent = GeminiChatBotService::generateContent($contents, function (Content $newCreatedContent) use ($chatHistory) {
                $chatHistory->contents()->create([
                    'content_serialized' => serialize($newCreatedContent),
                    'hidden' => true,
                ]);
            });

            // Save the last model response content (usually text response)
            $chatHistory->contents()->create([
                'content_serialized' => serialize($responseContent),
            ]);

            $responseTextOrObject = json_decode($responseContent->parts[0]->text) ?? $responseContent->parts[0]->text;
        });

        return $responseTextOrObject;
    }

    /**
     * Create a new chat history with the question instruction attached
     * @return ToeicChatHistory
     */
    public function createChatHistory($attemptId, $questionId)
    {
        $chatHistory = ToeicChatHistory::create([
            'toeic_test_attempt_id' => $attemptId,
            'question_id' => $questionId,
        ]);

        $this->appendQuestionInstructionContent($chatHistory, $questionId);

        return $chatHistory;
    }

    public function createChatHistoryByQuestionNumber($attemptId, $questionNumber)
    {
        $attempt = ToeicTestAttempt::findOrFail($attemptId);
        $question = $this->toeicTestService->getQuestionByNumber($attempt->toeic_test_id, $questionNumber);

        if (!$question) {
            throw ValidationException::withMessages([
                'question_number' => ['Question not found'],
            ]);
        }

        return $this->createChatHistory($attemptId, $question->id);
    }


    public function appendQuestionInstructionContent($chatHistoryIdOrInstance, $questionId)
    {
        $chatHistory = $chatHistoryIdOrInstance instanceof ToeicChatHistory ? $chatHistoryIdOrInstance : ToeicChatHistory::find($chatHistoryIdOrInstance);
        if (!$chatHistory) {
            return false;
        }

        // Create the first instruction from question
        $questionInstructionContext = GeminiChatBotService::createInstructionFromQuestion($questionId);
        $chatHistory->contents()->create([
            'content_serialized' => serialize($questionInstructionContext),
            'hidden' => true,
        ]);

        // Save last indexed question id
        $chatHistory->indexed_question_id = $questionId;
        $chatHistory->save();

        return true;
    }

    public function getChatHistory($attemptId, $questionId, $options)
    {
        $includeHidden = $options['include_hidden'] ?? false;

        $chatHistory = ToeicChatHistory::with('displayContents')
            ->where('toeic_test_attempt_id', $attemptId)
            ->where('question_id', $questionId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$chatHistory) {
            $chatHistory = $this->createChatHistory($attemptId, $questionId);
        }

        $returnedContents = [];
        if ($includeHidden) {
            $returnedContents = $chatHistory->contents;
        } else {
            $returnedContents = $chatHistory->displayContents;
        }


        $chatHistory->chatContents = $returnedContents->map(function ($contentModel) {
            $dtoContent = [
                "id" => $contentModel->id,
                "createdAt" => $contentModel->created_at,
            ];

            foreach ($contentModel->content->parts as $part) {
                $dtoContent["parts"][] = [
                    'text' => json_decode($part->text) ?? $part->text,
                    'inlineData' => $part->inlineData,
                    // 'fileData' => $part->fileData,
                    'functionCall' => $part->functionCall,
                    'functionResponse' => $part->functionResponse,
                    // 'executableCode' => $part->executableCode,
                    // 'codeExecutionResult' => $part->codeExecutionResult,
                ];
            }

            $dtoContent['role'] = $contentModel->content->role;

            return $dtoContent;
        });

        $chatHistory->makeHidden(['displayContents', 'contents']);

        return $chatHistory;
    }
}
