<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    /**
     * @param  array<int, array{role: string, text: string}>  $messages
     */
    public function reply(array $messages): string
    {
        $apiKey = config('services.gemini.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('Gemini API key is missing. Add GEMINI_API_KEY to your .env file.');
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $endpoint = rtrim(config('services.gemini.endpoint'), '/');
        $systemInstruction = config('services.gemini.system_instruction');

        $payload = [
            'contents' => collect($messages)
                ->map(fn (array $message): array => [
                    'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [
                        ['text' => $message['text']],
                    ],
                ])
                ->values()
                ->all(),
            'generationConfig' => [
                'temperature' => (float) config('services.gemini.temperature', 0.4),
                'maxOutputTokens' => (int) config('services.gemini.max_output_tokens', 600),
            ],
        ];

        if (filled($systemInstruction)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ];
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->post("{$endpoint}/{$model}:generateContent", $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Could not connect to Gemini. Please try again.', previous: $exception);
        }

        if ($response->failed()) {
            $message = $response->json('error.message') ?: 'Gemini returned an error.';

            throw new RuntimeException($message);
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')
            ->filter()
            ->implode("\n");

        if (blank($text)) {
            throw new RuntimeException('Gemini did not return a text response.');
        }

        return trim($text);
    }
}
