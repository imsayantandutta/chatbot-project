<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatController extends Controller
{
    public function __invoke(Request $request, GeminiService $gemini): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'history' => ['sometimes', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $history = collect($validated['history'] ?? [])
            ->map(fn (array $message): array => [
                'role' => $message['role'],
                'text' => trim($message['text']),
            ])
            ->filter(fn (array $message): bool => $message['text'] !== '')
            ->values()
            ->all();

        $messages = [
            ...$history,
            ['role' => 'user', 'text' => trim($validated['message'])],
        ];

        try {
            return response()->json([
                'reply' => $gemini->reply($messages),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => app()->hasDebugModeEnabled()
                    ? $exception->getMessage()
                    : 'Sorry, I could not generate a response right now.',
            ], 502);
        }
    }
}
