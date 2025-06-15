<?php

namespace App\Services;

use App\Enums\PartOfSpeech;
use Gemini\Data\Content;
use Gemini\Data\FunctionCall;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\FunctionResponse;
use Gemini\Data\Part;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\Role;

class FunctionCallingService
{
    public static function handleFunctionCall(FunctionCall $functionCall)
    {
        // Use Laravel's service container to resolve dependencies with proper DI
        $wordFolderService = app(WordFolderService::class);
        $lessonVocabularyService = app(LessonVocabularyService::class);

        switch ($functionCall->name) {
            case 'getWordFoldersOfUser':
                return new Content(
                    parts: [
                        new Part(
                            functionResponse: new FunctionResponse(
                                name: 'getWordFoldersOfUser',
                                response: ['word_folders' => $wordFolderService->getWordFoldersOfLoggedInUser()->toArray()],
                            )
                        )
                    ],
                    role: Role::USER
                );
            case 'addWordsToFolder':
                return new Content(
                    parts: [
                        new Part(
                            functionResponse: new FunctionResponse(
                                name: 'addWordsToFolder',
                                response: ['created_words' => $lessonVocabularyService->addWordsToFolder($functionCall->args['wordFolderId'], $functionCall->args['words'])],
                            )
                        )
                    ],
                    role: Role::USER
                );
            case 'getWordFolderDetails':
                return new Content(
                    parts: [
                        new Part(
                            functionResponse: new FunctionResponse(
                                name: 'getWordFolderDetails',
                                response: ['folder_details' => $wordFolderService->getWordFolderDetails($functionCall->args['wordFolderIdOrName'])->toArray()],
                            )
                        )
                    ],
                    role: Role::USER
                );
        }
    }

    //------------------------ DECLARATIONS -----------------------
    public static function getWordFoldersOfLoggedInUserDeclaration()
    {
        return new FunctionDeclaration(
            name: 'getWordFoldersOfUser',
            description: 'Fetches a list of all information of word folders created by the user. A word folder is a personal collection of saved words. This function should be called when the user expresses intent to view, access, or manage their word folders. For example, if the user says "Cho tôi xem các thư mục từ vựng của tôi", or "Tôi muốn thêm từ vựng vào thư mục của tôi".',
            response: new Schema(
                type: DataType::ARRAY ,
                items: new Schema(
                    type: DataType::OBJECT,
                    properties: [
                        'id' => new Schema(
                            type: DataType::NUMBER,
                            description: 'ID of the word folder',
                            example: 1
                        ),
                        'name' => new Schema(
                            type: DataType::STRING,
                            description: 'Name of the word folder',
                            example: "Từ khó part 1"
                        ),
                        'thumbnail' => new Schema(
                            type: DataType::STRING,
                            description: 'Thumbnail link of the word folder',
                            example: "https://example.com/thumbnail.jpg"
                        ),
                        'created_at' => new Schema(
                            type: DataType::STRING,
                            description: 'Created at of the word folder',
                            example: "2025-06-07T08:50:49.000000Z"
                        ),
                        'description' => new Schema(
                            type: DataType::STRING,
                            description: 'Description of the word folder',
                            example: "Used to store difficult words of part 1"
                        ),
                        'num_of_words' => new Schema(
                            type: DataType::NUMBER,
                            description: 'Number of words in the word folder',
                            example: 10
                        ),
                        'reserved_thumbnail' => new Schema(
                            type: DataType::STRING,
                            description: 'Reserved thumbnail link of the word folder',
                            example: "https://example.com/reserved_thumbnail.jpg"
                        )
                    ],
                    required: ['id', 'name', 'description', 'num_of_words']
                )
            )

        );
    }

    public static function addWordsToFolderDeclaration()
    {
        return new FunctionDeclaration(
            name: 'addWordsToFolder',
            description: 'Add words to a folder. This function should be called when the user expresses intent to add words to a folder. For example, if the user says "Thêm từ vựng này vào thư mục A của tôi".
            You can get word folders of the user by calling getWordFoldersOfUser function first and then give user options for which word folder to add words to.',
            parameters: new Schema(
                type: DataType::OBJECT,
                properties: [
                    'wordFolderId' => new Schema(
                        type: DataType::NUMBER,
                        description: 'ID of the word folder',
                        example: 1
                    ),
                    'words' => new Schema(
                        type: DataType::ARRAY ,
                        description: 'List of words to add to the folder. The "word" field is the only required field. You can include as much information as possible from the conversation context when calling this function.',
                        items: new Schema(
                            type: DataType::OBJECT,
                            properties: [
                                'word' => new Schema(
                                    type: DataType::STRING,
                                    description: 'Word in English',
                                    example: 'hello'
                                ),
                                'definition' => new Schema(
                                    type: DataType::STRING,
                                    description: 'Definition of the word in English',
                                    example: 'an expression of greeting'
                                ),
                                'meaning' => new Schema(
                                    type: DataType::STRING,
                                    description: 'Meaning in Vietnamese, short and concise',
                                    example: 'Xin chào'
                                ),
                                'pronunciation' => new Schema(
                                    type: DataType::STRING,
                                    description: 'Pronunciation of the word in English',
                                    example: '/həˈloʊ/'
                                ),
                                'example' => new Schema(
                                    type: DataType::STRING,
                                    description: 'Example in English. About 10 - 15 words',
                                    example: 'Hello, how are you?'
                                ),
                                'example_meaning' => new Schema(
                                    type: DataType::STRING,
                                    description: 'Meaning of the example in Vietnamese',
                                    example: 'Xin chào, bạn có khoẻ không?'
                                ),
                                'part_of_speech' => new Schema(
                                    type: DataType::STRING,
                                    enum: PartOfSpeech::values(),
                                    description: 'Part of speech of the word',
                                    example: 'verb'
                                ),
                            ],
                            required: ['word']
                        )
                    ),
                ],
                required: ['wordFolderId', 'words']
            )
        );
    }

    public static function getWordFolderDetailsDeclaration()
    {
        return new FunctionDeclaration(
            name: 'getWordFolderDetails',
            description: 'Retrieves detailed information about a specific word folder including all words inside the folder. This function should be called when the user wants to view the contents of a specific folder, see all words in a folder, or get detailed information about a folder. For example, if the user says "Cho tôi xem chi tiết thư mục A", "Hiển thị tất cả từ vựng trong thư mục B", or "Tôi muốn xem nội dung của thư mục từ vựng này".',
            parameters: new Schema(
                type: DataType::OBJECT,
                properties: [
                    'wordFolderIdOrName' => new Schema(
                        type: DataType::STRING,
                        description: 'ID or name of the word folder to get details for. Can be either a numeric ID (as string) or the folder name.',
                        example: '1'
                    ),
                ],
                required: ['wordFolderIdOrName']
            ),
            response: new Schema(
                type: DataType::OBJECT,
                properties: [
                    'folder_details' => new Schema(
                        type: DataType::OBJECT,
                        properties: [
                            'id' => new Schema(
                                type: DataType::NUMBER,
                                description: 'ID of the word folder',
                                example: 1,
                            ),
                            'name' => new Schema(
                                type: DataType::STRING,
                                description: 'Name of the word folder',
                                example: 'My TOEIC Vocabulary'
                            ),
                            'description' => new Schema(
                                type: DataType::STRING,
                                description: 'Description of the word folder',
                                example: 'Collection of TOEIC vocabulary words'
                            ),
                            'num_of_words' => new Schema(
                                type: DataType::NUMBER,
                                description: 'Total number of words in this folder',
                                example: 25
                            ),
                            'created_at' => new Schema(
                                type: DataType::STRING,
                                description: 'Created at of the word folder',
                                example: "2025-06-07T08:50:49.000000Z"
                            ),
                            'words' => new Schema(
                                type: DataType::ARRAY ,
                                description: 'List of words in this folder',
                                items: new Schema(
                                    type: DataType::OBJECT,
                                    properties: [
                                        'id' => new Schema(
                                            type: DataType::NUMBER,
                                            description: 'ID of the lesson vocabulary entry',
                                            example: 1
                                        ),
                                        'word' => new Schema(
                                            type: DataType::STRING,
                                            description: 'The English word',
                                            example: 'hello'
                                        ),
                                        'meaning' => new Schema(
                                            type: DataType::STRING,
                                            description: 'Vietnamese meaning of the word',
                                            example: 'Xin chào'
                                        ),
                                        'definition' => new Schema(
                                            type: DataType::STRING,
                                            description: 'English definition of the word',
                                            example: 'an expression of greeting'
                                        ),
                                        'pronunciation' => new Schema(
                                            type: DataType::STRING,
                                            description: 'Pronunciation of the word',
                                            example: '/həˈloʊ/'
                                        ),
                                        'part_of_speech' => new Schema(
                                            type: DataType::STRING,
                                            description: 'Part of speech',
                                            example: 'interjection'
                                        ),
                                        'example' => new Schema(
                                            type: DataType::STRING,
                                            description: 'Example sentence using the word',
                                            example: 'Hello, how are you today?'
                                        ),
                                        'example_meaning' => new Schema(
                                            type: DataType::STRING,
                                            description: 'Vietnamese translation of the example',
                                            example: 'Xin chào, hôm nay bạn có khỏe không?'
                                        ),
                                    ]
                                ),
                            ),
                        ]
                    )
                ]
            )
        );
    }
    //------------------------ END DECLARATIONS -----------------------



    public static function getAvailableFunctionDeclarations()
    {
        return [
            self::getWordFoldersOfLoggedInUserDeclaration(),
            self::addWordsToFolderDeclaration(),
            self::getWordFolderDetailsDeclaration(),
        ];
    }
}
