<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TtsService
{
    /**
     * Synthesize speech from text using Azure TTS API.
     */
    public function synthesize(string $text): string
    {
        $endpoint = config('services.azure_tts.endpoint');
        $key = config('services.azure_tts.key');
        $outputFormat = 'audio-16khz-32kbitrate-mono-mp3';

        $ssml = $this->constructSsml($text);

        $response = Http::withHeaders([
            'Content-Type' => 'application/ssml+xml',
            'Ocp-Apim-Subscription-Key' => $key,
            'X-Microsoft-OutputFormat' => $outputFormat,
        ])->withBody($ssml, 'application/ssml+xml')->post($endpoint);

        if (!$response->successful()) {
            abort($response->status(), 'TTS service error: ' . $response->body());
        }

        return $response->body();
    }

    /**
     * Construct SSML body for Azure TTS.
     */
    private function constructSsml(string $text): string
    {
        return "<speak version='1.0' xml:lang='en-US'><voice xml:lang='en-US' xml:gender='Male' name='en-US-ChristopherNeural'>" .
            e($text) .
            "</voice></speak>";
    }
}
