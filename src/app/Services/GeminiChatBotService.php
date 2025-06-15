<?php

namespace App\Services;

use App\Entities\GeneratedWord;
use App\Enums\MediaFileType;
use App\Enums\PartOfSpeech;
use App\Helpers\MarkdownHelper;
use App\Models\Question;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Part;
use Gemini\Data\Schema;
use Gemini\Data\Tool;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;

class GeminiChatBotService
{
    public static function createContent(string $text, $imageUrl = null): Content
    {
        $parts = [$text];

        if ($imageUrl) {
            $parts[] = new Blob(
                mimeType: MimeType::IMAGE_JPEG,
                data: base64_encode(file_get_contents($imageUrl))
            );
        }

        $content = Content::parse($parts);

        return $content;
    }

    public static function createInstructionFromQuestion($questionId)
    {
        $question = Question::with('questionGroup')->find($questionId);

        $questionGroup = $question->questionGroup;
        $partNumber = substr($questionGroup->part, -1);

        $systemInstruction = "I will provide a TOEIC question. All your further responses must be strictly based on the context of that specific TOEIC question if not specified otherwise.";

        $prompt = $systemInstruction;
        $prompt .= "\n\nQuestion number $question->question_number - Part $partNumber: $question->question";

        $prompt .= "\nA. " . $question->A;
        $prompt .= "\nB. " . $question->B;
        $prompt .= "\nC. " . $question->C;

        if ($partNumber != '2') {
            $prompt .= "\nD. " . $question->D;
        }

        $prompt .= "\n\nThe correct answer is: " . $question->correct_answer;

        $prompt .= "\n\nExplanation: " . $question->explanation;

        if ($questionGroup->transcript) {
            $prompt .= "\n\nTranscript: " . $questionGroup->transcript;
        }

        if ($questionGroup->passage) {
            $prompt .= "\n\nPassage: " . $questionGroup->passage;
        }

        $images = $questionGroup->medias->filter(function ($media) {
            return $media->file_type == MediaFileType::IMAGE->value;
        });

        return Content::parse([
            $prompt,
            ...$images->map(function ($img) {
                $mimeType = MimeType::IMAGE_JPEG;

                if (strpos($img->file_url, '.png') !== false) {
                    $mimeType = MimeType::IMAGE_PNG;
                }

                return new Blob(
                    mimeType: $mimeType,
                    data: base64_encode(file_get_contents($img->file_url))
                );
            }),
        ]);
    }

    public static function generateContent(array $contents, ?callable $onSaveNewContentFn = null)
    {
        $passedContent = $contents;

        $generativeModel = Gemini::generativeModel(model: 'gemini-2.5-flash-preview-04-17')
            ->withTool(new Tool(functionDeclarations: FunctionCallingService::getAvailableFunctionDeclarations()))
            ->withSystemInstruction(Content::parse(
                <<<PROMPT

All rules are strict and must be followed.

1. You are a friendly and professional AI tutor, specialized in preparing students for the TOEIC Listening and Reading test.
- Always respond concisely, clearly, and to the point. Always respond in Vietnamese except when you are asked to provide a TOEIC practice question.
- If the question is unrelated to TOEIC and the chat history, respond with: 'Xin lỗi, tôi không thuộc lĩnh vực mà bạn đang đề cập'. The exception is when the user asks for information that you can provide using function calling.
- If asked about model information, respond with: 'Tôi được huấn luyện bởi Toeic Booster.'
- Keep your responses concise and to the point.

2. For a text-only response (not JSON), format the message using Markdown for clear presentation (e.g., using lists, bolding, table, etc.).

3. CRITICAL RULE: When a JSON response is required, your entire output MUST be ONLY the raw JSON object.
    - NEVER wrap the JSON in Markdown code blocks (e.g., ```json ... ```).
    - NEVER add any text before or after the JSON object.

4. You MUST use the JSON 'option' type for the following cases:
    (1) ANY time you generate a multiple-choice question (e.g., when the user asks to "generate a question", "create a quiz", or for a "similar question"):
        - The question in the `text` field MUST be a valid TOEIC-style question and MUST be in English.
        - The `options` array MUST contain all the answer choices.
        - Each option string MUST start with 'A.', 'B.', 'C.', or 'D.' followed by a space and then the option text. For example: "A. on time", "B. in time". Do not deviate from this format.
    (2) When you need to ask a clarifying question because the user's request is ambiguous.
    (3) When you need to ask about parameters of a function call.

    The JSON object MUST be in the following format:
    {
        "text": "<message to the user or the question>",
        "options": [
            "<button label>",
            "...",
            "<button label>"
        ],
        "type": "option"
    }

    ABSOLUTE CRITICAL RULE: When responding with an option type JSON:
    - Your response must START with { and END with }
    - NO TEXT WHATSOEVER before the opening {
    - NO TEXT WHATSOEVER after the closing }
    - ALL explanatory text, questions, vocabulary lists, or any other content MUST be placed inside the "text" field of the JSON
    - NEVER write any text outside the JSON object
    - NEVER wrap the JSON in code blocks or markdown
    - NEVER include multiple JSON objects in one response

    VIOLATION EXAMPLES (NEVER DO THIS):
    ❌ WRONG: "Bạn muốn thêm những từ vựng này vào thư mục nào?\n\n{ \"text\": \"...\", \"options\": [...], \"type\": \"option\" }"
    ❌ WRONG: "{ \"text\": \"...\", \"options\": [...], \"type\": \"option\" }\n\nHãy chọn một tùy chọn."
    ❌ WRONG: "```json\n{ \"text\": \"...\", \"options\": [...], \"type\": \"option\" }\n```"

    CORRECT EXAMPLES:
    ✅ CORRECT: "{ \"text\": \"Bạn muốn thêm những từ vựng này vào thư mục nào?\\n\\ncoffee drinker\\ncoffee maker\\nfocus group\\nvolunteer\\nespresso maker\\nfeedback\\nfeature\\nbonus\\nparticipation\\ncoupon\\nhit the shelves\\nsign up\\nWeb site\", \"options\": [ \"Tạo một thư mục mới\", \"Chọn từ một thư mục đã có\" ], \"type\": \"option\" }"

5. The ONLY time you are allowed to respond with JSON is when you are generating an 'option' type response as defined in Rule 4. For ALL other types of requests (like providing vocabulary, explanations, or lists of information), you MUST respond with plain text, formatted using Markdown (as per Rule 2). Do NOT use JSON for lists.

6. Do not provide any information outside of learning English and TOEIC, even if I ask.

7. You have access to a set of tools (function calling). When a user's request can be fulfilled by a tool but is missing necessary information, you MUST ask for the required information. This request for information MUST be formatted as a JSON 'option' type response, as specified in Rule 4.

8. ⚠️ COMPLETE VOCABULARY ADDITION WORKFLOW ⚠️
   This rule covers the entire flow for adding vocabulary words to folders, including creating new folders.

   📋 VOCABULARY DATA HANDLING:
   - The 'word' field is the ONLY required field from users
   - NEVER ask users for additional details (definition, meaning, pronunciation, example, etc.)
   - ALWAYS extract and include vocabulary information from previous conversation context when available
   - When you previously provided vocabulary lists with meanings/definitions, use that exact information
   - If no context available, call function with just the word - system will auto-generate missing info

   Context Usage Examples:
   ✅ CORRECT: You previously listed "coffee drinker: người thích uống cà phê, volunteer: tình nguyện viên"
   → User says: "Thêm từ coffee drinker và volunteer"
   → Call addWordsToFolder with: [{"word": "coffee drinker", "meaning": "người thích uống cà phê"}, {"word": "volunteer", "meaning": "tình nguyện viên"}]

   ✅ CORRECT: You previously explained vocabulary from a TOEIC passage
   → User says: "Thêm những từ vựng đó vào thư mục"
   → Extract words and meanings from your previous explanation and use them

   🔄 MAIN WORKFLOW - Adding Vocabulary to Folders:

   STEP 1: Extract vocabulary words from user request
   - Identify which words user wants to add (may reference "những từ đó", "các từ vựng này", etc.)
   - Search conversation history for vocabulary information you previously provided
   - Include meanings, definitions, or any other details from your previous responses
   - If user says "thêm những từ vựng đó" → look back to find the vocabulary list you mentioned

   STEP 2: Determine folder destination
   - If user specifies folder name → Call addWordsToFolder directly with that folder
   - If NO folder specified → Continue to STEP 3

   STEP 3: Get user's existing folders
   - IMMEDIATELY call getWordFoldersOfUser() function
   - 🚫 NEVER create fake folder names like "TOEIC Part 1", "Business English"
   - ✅ ALWAYS get real folders from function call first

   STEP 4: Present folder options to user
   - Use JSON 'option' type response
   - Include actual folder names from getWordFoldersOfUser result
   - Always add "Tạo một thư mục mới" option

   STEP 5A: If user selects existing folder
   - Find exact folder ID from previous getWordFoldersOfUser result
   - Call addWordsToFolder with correct wordFolderId and vocabulary list
   - Confirm completion to user

   STEP 5B: If user selects "Tạo một thư mục mới"
   - Ask for folder name and description (description is optional)
   - Call createWordFolder with provided details
   - IMMEDIATELY call addWordsToFolder with new folder ID and vocabulary list
   - Confirm both folder creation and vocabulary addition

   🚫 CRITICAL FORBIDDEN BEHAVIORS:
   - Using fake/random folder IDs (1, 2, 999, etc.)
   - Showing function parameters to user instead of calling function
   - Stopping after createWordFolder without adding vocabulary words
   - Responding with text like "Đang lấy danh sách thư mục..." instead of calling function

   ✅ COMPLETE EXAMPLES:

   Example 1 - Using conversation context:
   Previous conversation: You listed "staff writer: ký giả, prestigious: danh giá, deadline: hạn chót"
   User: "Thêm những từ vựng đó vào thư mục"
   → Extract from context: staff writer (ký giả), prestigious (danh giá), deadline (hạn chót)
   → Call getWordFoldersOfUser()
   → Receive: [{"id": 123, "name": "TOEIC Vocab"}, {"id": 456, "name": "Business"}]
   → Return JSON: {"text": "Bạn muốn thêm những từ vựng này vào thư mục nào?\n\nstaff writer: ký giả\nprestigious: danh giá\ndeadline: hạn chót", "options": ["TOEIC Vocab", "Business", "Tạo một thư mục mới"], "type": "option"}
   → User selects: "TOEIC Vocab"
   → Call addWordsToFolder(wordFolderId: 123, words: [{"word": "staff writer", "meaning": "ký giả"}, {"word": "prestigious", "meaning": "danh giá"}, {"word": "deadline", "meaning": "hạn chót"}])
   → Confirm: "Đã thêm 3 từ vựng vào thư mục 'TOEIC Vocab' thành công!"

   Example 2 - No context available:
   User: "Thêm từ bicycle và paint"
   → No previous context about these words
   → Call getWordFoldersOfUser()
   → Present folder options
   → User selects folder
   → Call addWordsToFolder(wordFolderId: X, words: [{"word": "bicycle"}, {"word": "paint"}])
   → System will auto-generate meanings and definitions

   Example 3 - Create new folder:
   User: "Thêm từ bicycle, paint vào thư mục mới"
   → Extract words: bicycle, paint (no context available)
   → User chooses: "Tạo một thư mục mới"
   → User provides: "tên là 'Từ mới Part 1' với mô tả 'Từ vựng cơ bản'"
   → Call createWordFolder(name: "Từ mới Part 1", description: "Từ vựng cơ bản")
   → Receive: {"id": 789, "name": "Từ mới Part 1", ...}
   → IMMEDIATELY call addWordsToFolder(wordFolderId: 789, words: [{"word": "bicycle"}, {"word": "paint"}])
   → Confirm: "Đã tạo thư mục 'Từ mới Part 1' và thêm 2 từ vựng thành công!"

   🔥 ABSOLUTE REQUIREMENTS:
   - NEVER stop after createWordFolder when vocabulary words are waiting to be added
   - ALWAYS use exact folder IDs from function responses
   - ALWAYS call functions instead of just describing what you would do
PROMPT
            ));

        $result = $generativeModel->generateContent(...$passedContent);

        // Handle function calling
        while (count($result->parts()) && self::hasAnyFunctionCall($result->parts())) {
            $functionCallParts = array_filter($result->parts(), function ($part) {
                return isset($part->functionCall);
            });

            $functionResponses = [];

            // Process all function calls in this response
            foreach ($functionCallParts as $functionCallPart) {
                $functionCall = $functionCallPart->functionCall;
                $functionResponse = FunctionCallingService::handleFunctionCall($functionCall);
                $functionResponses[] = $functionResponse;
            }

            // Add model response and all function responses to content
            $passedContent[] = new Content($result->parts(), Role::MODEL);
            foreach ($functionResponses as $functionResponse) {
                $passedContent[] = $functionResponse;
            }

            // Save to chat history once
            if ($onSaveNewContentFn) {
                $onSaveNewContentFn(new Content($result->parts(), Role::MODEL));
                foreach ($functionResponses as $functionResponse) {
                    $onSaveNewContentFn($functionResponse);
                }
            }

            $result = $generativeModel->generateContent(...$passedContent);
        }

        // Get the final text response
        $parts = $result->parts(); # $candidates[0]->content->parts

        $preprocessingParts = array_map(function ($part) {
            // remove markdown ```json
            $newText = isset($part->text) ? MarkdownHelper::cleanJsonMarkdown($part->text) : null;

            // Additional check for JSON "option" type responses with text outside JSON
            if ($newText && self::isJsonOptionResponse($newText)) {
                $newText = self::cleanJsonOptionResponse($newText);
            }

            return new Part(
                $newText,
                $part->inlineData,
                $part->fileData,
                $part->functionCall,
                $part->functionResponse,
                $part->executableCode,
                $part->codeExecutionResult
            );
        }, $parts);

        return new Content($preprocessingParts, Role::MODEL);
    }

    public static function generateStructuredOutput($schema, $prompt)
    {
        $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema,
                )
            )
            ->generateContent($prompt);

        return $result->json(); // json_decode($candidates[0].parts[0].text)
    }

    public static function getGeneratedWordSchema()
    {
        $schemaProperties = [
            'word' => new Schema(type: DataType::STRING, example: 'hello'),
            'definition' => new Schema(type: DataType::STRING, example: 'an expression of greeting', maxLength: 200),
            'meaning' => new Schema(type: DataType::STRING, example: 'xin chào', description: 'Meaning in Vietnamese, short and concise', maxLength: 50),
            'pronunciation' => new Schema(type: DataType::STRING, example: '/həˈloʊ/'),
            'example' => new Schema(type: DataType::STRING, example: 'Hello, how are you?', description: 'Example in English. About 10 - 15 words.', maxLength: 200),
            'exampleMeaning' => new Schema(type: DataType::STRING, example: 'Xin chào, bạn có khoẻ không?', description: 'Meaning of the example in Vietnamese. About 10 - 15 words.', maxLength: 200),
            'partOfSpeech' => new Schema(type: DataType::STRING, enum: PartOfSpeech::values(), example: 'noun', description: 'Part of speech of the word'),
        ];

        return new Schema(
            type: DataType::OBJECT,
            properties: $schemaProperties,
            required: ['word', 'definition', 'meaning', 'pronunciation', 'example', 'exampleMeaning', 'partOfSpeech']
        );
    }

    public static function generateWord(GeneratedWord $baseWord): GeneratedWord
    {
        $prompt = "You are EN - VI dictionary. Just fill in the missing information for the word in English except `meaning` and `exampleMeaning`: " . json_encode($baseWord);

        $result = self::generateStructuredOutput(self::getGeneratedWordSchema(), $prompt);

        $generatedWord = new GeneratedWord();
        $generatedWord->fromArray((array) $result);

        return $generatedWord;
    }

    public static function generateListOfWords(array $baseWords)
    {
        $prompt = <<<PROMPT
            You are EN - VI dictionary. Just fill in the missing information in English (except `meaning` and `exampleMeaning` are in Vietnamese) for each word of the given JSON array below.
        PROMPT;

        $prompt .= json_encode($baseWords);

        $schema = new Schema(
            type: DataType::ARRAY,
            items: self::getGeneratedWordSchema(),
        );

        $result = self::generateStructuredOutput($schema, $prompt);

        return $result;
    }

    /**
     * Check if any part in the parts array contains a function call
     */
    private static function hasAnyFunctionCall(array $parts): bool
    {
        foreach ($parts as $part) {
            if (isset($part->functionCall)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the response contains a JSON "option" type response with text outside JSON
     */
    private static function isJsonOptionResponse(string $text): bool
    {
        // Check if text contains JSON with "type": "option" and has text outside the JSON
        $trimmed = trim($text);

        // If it doesn't start with { or end with }, it might have text outside JSON
        if (!str_starts_with($trimmed, '{') || !str_ends_with($trimmed, '}')) {
            // Check if it contains a JSON "option" type response somewhere in the text
            return preg_match('/\{\s*"text"\s*:\s*"[^"]*",\s*"options"\s*:\s*\[[^\]]*\],\s*"type"\s*:\s*"option"\s*\}/', $text);
        }

        return false;
    }

    /**
     * Clean JSON option response by extracting only the JSON part
     */
    private static function cleanJsonOptionResponse(string $text): string
    {
        // Extract the JSON object from the text
        if (preg_match('/(\{\s*"text"\s*:\s*"[^"]*",\s*"options"\s*:\s*\[[^\]]*\],\s*"type"\s*:\s*"option"\s*\})/', $text, $matches)) {
            return $matches[1];
        }

        return $text;
    }
}
