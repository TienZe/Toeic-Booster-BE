<?php

namespace App\Http\Controllers;

use App\Http\Requests\TtsRequest;
use App\Services\TtsService;
use Illuminate\Http\Response;

final class TtsController extends Controller
{
    /**
     * Handle the TTS request and return audio stream.
     */
    public function __invoke(TtsRequest $request, TtsService $ttsService): Response
    {
        $audioStream = $ttsService->synthesize($request->validated()['text']);

        return response($audioStream, 200)
            ->header('Content-Type', 'audio/mp3');
    }
}
