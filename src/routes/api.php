<?php

use App\Http\Controllers\CollectionTagController;
use App\Http\Controllers\LessonLearningController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\LessonVocabularyController;

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
    Route::get('/', [CollectionController::class, 'index']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::get('/{id}', [CollectionController::class, 'show']);
    Route::put('/{id}', [CollectionController::class, 'update']);
    Route::delete('/{id}', [CollectionController::class, 'destroy']);

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
        Route::get('/', [LessonVocabularyController::class, 'index']);
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
