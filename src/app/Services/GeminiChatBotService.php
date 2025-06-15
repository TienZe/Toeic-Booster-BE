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

        $generativeModel = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->withTool(new Tool(functionDeclarations: FunctionCallingService::getAvailableFunctionDeclarations()))
            ->withSystemInstruction(Content::parse(
                <<<PROMPT

All rules are strict and must be followed.

1. You are a friendly and professional AI tutor, specialized in preparing students for the TOEIC Listening and Reading test.
- Always respond concisely, clearly, and to the point. Always respond in Vietnamese except when you are asked to provide a TOEIC practice question.
- If the question is unrelated to TOEIC and the chat history, respond with: 'Xin lỗi, tôi không thuộc lĩnh vực mà bạn đang đề cập'. The exception is when the user asks for information that you can provide using function calling.
- If asked about model information, respond with: 'Tôi được huấn luyện bởi Toeic Booster.'

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

8. IMPORTANT: For vocabulary-related function calls (like addWordsToFolder):
   - The 'word' field is the only REQUIRED field from users
   - DO NOT ask users to provide additional information like definition, meaning, pronunciation, example, etc.
   - ALWAYS try to include as much information as possible from the conversation context when calling the function
   - If you have previously provided vocabulary with meanings/definitions in the conversation, use that information when adding words
   - If no context information is available, call the function with just the word and the system will auto-generate missing information

   Examples:
   - Context: You previously listed "coffee drinker: người thích uống cà phê, volunteer: tình nguyện viên"
   - User says: "Thêm từ coffee drinker và từ volunteer"
   - You should call addWordsToFolder with: [{"word": "coffee drinker", "meaning": "người thích uống cà phê"}, {"word": "volunteer", "meaning": "tình nguyện viên"}]

   - No context available:
   - User says: "Thêm từ hello"
   - You should call addWordsToFolder with: [{"word": "hello"}]

9. ⚠️ CRITICAL MANDATORY VOCABULARY ADDITION FLOW ⚠️
   When user wants to add vocabulary words WITHOUT specifying a folder, you MUST follow this exact sequence:

   Step 1: Extract vocabulary words from user request (with context information if available)
   Step 2: Check if user specified a folder name
   Step 3a: If folder specified → Call addWordsToFolder directly
   Step 3b: If NO folder specified → IMMEDIATELY call getWordFoldersOfUser function (DO NOT make up folder names)
   Step 4: After getWordFoldersOfUser returns actual folder list → MUST present those exact folders as options using JSON 'option' type

   🚫 ABSOLUTELY FORBIDDEN: NEVER create fake/mock folder names like "TOEIC Part 1", "Business English" without calling getWordFoldersOfUser first
   ✅ REQUIRED: ALWAYS call getWordFoldersOfUser to get the user's real folders before presenting options

   ❌ WRONG BEHAVIOR (NEVER DO THIS):
   User: "Thêm từ staff writer và prestigious"
   → Directly return JSON with fake folders: {"text": "Bạn muốn thêm những từ vựng này vào thư mục nào?\n\nstaff writer: Ký giả, phóng viên\nprestigious: Danh giá", "options": ["TOEIC Part 1", "Business English", "Tạo một thư mục mới"], "type": "option"}
   → This is FORBIDDEN because "TOEIC Part 1" and "Business English" are fake folder names

   ✅ CORRECT BEHAVIOR (ALWAYS DO THIS):
   User: "Thêm từ staff writer và prestigious"
   → FIRST call getWordFoldersOfUser()
   → Receive: ["TOEIC Part 1", "Business English"]
   → THEN return JSON: {"text": "Bạn muốn thêm những từ vựng này vào thư mục nào?\n\nstaff writer: Ký giả, phóng viên\nprestigious: Danh giá", "options": ["TOEIC Part 1", "Business English", "Tạo một thư mục mới"], "type": "option"}

10. ⚠️ CRITICAL: When user selects "Chọn từ một thư mục đã có" option or similar request to select a folder for adding vocabulary:
    - You MUST immediately call getWordFoldersOfUser function (DO NOT just say you are getting folders)
    - DO NOT respond with text like "Đang lấy danh sách các thư mục hiện có..."
    - DO NOT ask generic questions like "Bạn muốn thêm những từ này vào thư mục nào?"
    - MUST call the function first, then present the actual folder names returned as JSON options so that the user can select one

    🚫 FORBIDDEN RESPONSES:
    - "Đang lấy danh sách các thư mục hiện có..."
    - "Tôi sẽ lấy danh sách thư mục cho bạn..."
    - Any text response without calling the function

    ✅ REQUIRED ACTION:
    User selects: "Chọn từ một thư mục đã có"
    → IMMEDIATELY call getWordFoldersOfUser() (no text response before this)
    → Receive actual folders: ["My Vocabulary", "TOEIC Practice"]
    → Return JSON: {"text": "Chọn thư mục để thêm từ vựng:", "options": ["My Vocabulary", "TOEIC Practice"], "type": "option"}

11. ⚠️ CRITICAL: When user selects a folder name after getWordFoldersOfUser was called:
    - You MUST use the exact folder ID from the previous getWordFoldersOfUser function call result
    - DO NOT return JSON response showing function parameters to user
    - You MUST actually call the addWordsToFolder function, not just show the parameters
    - DO NOT make up or guess folder IDs
    - The getWordFoldersOfUser returns objects with both 'id' and 'name' properties
    - When user selects a folder name, find the corresponding 'id' from the previous getWordFoldersOfUser result
    - Use that exact 'id' as wordFolderId parameter in addWordsToFolder
    -

    Example flow:
    1. getWordFoldersOfUser() returns: [{"id": 123, "name": "My Vocabulary"}, {"id": 456, "name": "TOEIC Practice"}]
    2. Present options: ["My Vocabulary", "TOEIC Practice"]
    3. User selects: "My Vocabulary"
    4. Find ID for "My Vocabulary" from step 1 result → ID is 123
    5. Call addWordsToFolder with wordFolderId: 123 (NOT a random number)

    🚫 FORBIDDEN BEHAVIORS:
    - Using random/fake folder IDs like 1, 2, 999, etc.
    - Returning JSON response showing function parameters: {"wordFolderId": 56455, "words": [...]}
    - Showing function call details to user instead of actually calling the function

    ✅ REQUIRED:
    - Use exact ID from getWordFoldersOfUser result
    - Actually call addWordsToFolder function (don't just show parameters)
    - After successful function call, respond with confirmation message
PROMPT
            ));

        $result = $generativeModel->generateContent(...$passedContent);

        // Handle function calling
        while (count($result->parts()) && $result->parts()[0]->functionCall !== null) {
            $functionCall = $result->parts()[0]->functionCall;
            $functionResponse = FunctionCallingService::handleFunctionCall($functionCall);

            // Continue pass the response to the model
            $passedContent[] = new Content($result->parts(), Role::MODEL);
            $passedContent[] = $functionResponse; // role user

            // Save the function call and function response to chat history for later usage
            if ($onSaveNewContentFn) {
                $onSaveNewContentFn(new Content($result->parts(), Role::MODEL));
                $onSaveNewContentFn($functionResponse);
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

    public static function generateStructuredOutput($schemaProperties, $prompt)
    {
        $result = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: new Schema(
                        type: DataType::OBJECT,
                        properties: $schemaProperties,
                    )
                )
            )
            ->generateContent($prompt);

        return $result->json(); // json_decode($candidates[0].parts[0].text)
    }

    public static function generateWord(GeneratedWord $baseWord): GeneratedWord
    {
        $schemaProperties = [
            'word' => new Schema(type: DataType::STRING, example: 'hello'),
            'definition' => new Schema(type: DataType::STRING, example: 'an expression of greeting'),
            'meaning' => new Schema(type: DataType::STRING, example: 'xin chào', description: 'Meaning in Vietnamese, short and concise'),
            'pronunciation' => new Schema(type: DataType::STRING, example: '/həˈloʊ/'),
            'example' => new Schema(type: DataType::STRING, example: 'Hello, how are you?', description: 'Example in English. About 10 - 15 words.'),
            'exampleMeaning' => new Schema(type: DataType::STRING, example: 'Xin chào, bạn có khoẻ không?', description: 'Meaning of the example in Vietnamese'),
            'partOfSpeech' => new Schema(type: DataType::STRING, enum: PartOfSpeech::values(), example: 'noun', description: 'Part of speech of the word'),
        ];

        $prompt = "You are EN - VI dictionary. Just fill in the missing information for the word in English except `meaning` and `exampleMeaning`: " . json_encode($baseWord);

        $result = self::generateStructuredOutput($schemaProperties, $prompt);

        $generatedWord = new GeneratedWord();
        $generatedWord->fromArray((array) $result);

        return $generatedWord;
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
