<?php

use App\Http\Controllers\CollectionTagController;
use App\Http\Controllers\LessonLearningController;
use App\Http\Controllers\ToeicTestController;
use App\Http\Controllers\WordFolderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\LessonVocabularyController;
use App\Http\Controllers\LessonExamController;
use App\Http\Controllers\TtsController;
use App\Http\Controllers\CollectionRatingController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::group([
    'prefix' => 'user',
    'middleware' => 'jwt.auth'
], function () {
    Route::put('profile', [UserController::class, 'updateProfile']);
});

Route::group([
    'prefix' => 'collections',
    // 'middleware' => 'jwt.auth'
], function () {
    Route::middleware('jwt.auth')->group(function () {
        Route::get('/recommend', [CollectionController::class, 'recommendCollections']);
        Route::get('/user-might-also-like', [CollectionController::class, 'getCollectionUserMightAlsoLike']);
    });

    Route::get('/{id}/similar', [CollectionController::class, 'getSimilarCollections']);

    Route::get('/', [CollectionController::class, 'index']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::get('/{id}', [CollectionController::class, 'show']);
    Route::put('/{id}', [CollectionController::class, 'update']);
    Route::delete('/{id}', [CollectionController::class, 'destroy']);


    Route::group([
        'prefix' => '{collectionId}/ratings',
        'middleware' => 'jwt.auth'
    ], function () {
        Route::post('/', [CollectionRatingController::class, 'store']);
        Route::get('/', [CollectionRatingController::class, 'index']);
    });

    Route::group([
        'prefix' => '{collection}/lessons',
    ], function () {
        Route::get('/', [LessonController::class, 'index']);
        Route::post('/', [LessonController::class, 'store']);
    });
});

Route::group([
    'prefix' => 'lessons',
    // 'middleware' => 'jwt.auth'
], function () {
    Route::get('/{id}', [LessonController::class, 'show']);
    Route::put('/{id}', [LessonController::class, 'update']);
    Route::delete('/{id}', [LessonController::class, 'destroy']);


    Route::group([
        'prefix' => '{lessonId}/words',
    ], function () {
        Route::get('/', [LessonVocabularyController::class, 'getLessonVocabularies']);
        Route::post('/', [LessonVocabularyController::class, 'store']);
        Route::delete('/{vocabularyId}', [LessonVocabularyController::class, 'destroy']);
    });

    Route::group([
        'prefix' => '{lessonId}/lesson-learnings',
        'middleware' => 'jwt.auth'
    ], function () {
        Route::post('/', [LessonLearningController::class, 'save']);
    });

    Route::group([
        'prefix' => '{lessonId}',
        'middleware' => 'jwt.auth'
    ], function () {
        Route::get('/filtering-result', [LessonLearningController::class, 'getUserLessonVocabularyFilteringResult']);
        Route::get('/practice-statistics', [LessonExamController::class, 'getLessonPracticeStatistics']);
    });
});

Route::group([
    'prefix' => 'vocabularies',
    // 'middleware' => 'jwt.auth'
], function () {
    Route::get('/', [VocabularyController::class, 'index']);
    Route::post('/', [VocabularyController::class, 'store']);
    Route::get('/{id}', [VocabularyController::class, 'show']);
    Route::put('/{id}', [VocabularyController::class, 'update']);
    Route::delete('/{id}', [VocabularyController::class, 'destroy']);
});

Route::group([
    'prefix' => 'collection-tags',
], function () {
    Route::get('/', [CollectionTagController::class, 'index']);
});

Route::group([
    'prefix' => 'lesson-exams',
    'middleware' => 'jwt.auth'
], function () {
    Route::post('/', [LessonExamController::class, 'store']);
});

Route::group([
    'prefix' => 'word-folders',
    'middleware' => 'jwt.auth'
], function () {
    Route::get('/', [WordFolderController::class, 'index']);
    Route::post('/', [WordFolderController::class, 'store']);
    Route::put('/{id}', [WordFolderController::class, 'update']);
    Route::delete('/{id}', [WordFolderController::class, 'destroy']);
});

Route::group([
    'prefix' => 'lesson-vocabularies',
], function () {
    Route::delete('/{lessonVocabularyId}', [LessonVocabularyController::class, 'delete']);
});

Route::group([
    'prefix' => 'toeic-tests',
], function () {
    Route::post('/', [ToeicTestController::class, 'save']);
    Route::get('/', [ToeicTestController::class, 'index']);
    Route::get('/{id}/info', [ToeicTestController::class, 'getToeicTestInfo']);
    Route::get('/{id}', [ToeicTestController::class, 'show']);
});

Route::post('/tts', TtsController::class);
