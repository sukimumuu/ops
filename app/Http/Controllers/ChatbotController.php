<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\Property;
use App\Services\Chatbot\ChatbotService;
use App\Services\Chatbot\ChatbotUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(private ChatbotService $chatbotService) {}

    public function ask(Request $request): JsonResponse
    {
        $unexpectedFields = array_diff(array_keys($request->all()), ['message', 'conversation_id']);

        if ($unexpectedFields !== []) {
            return response()->json([
                'message' => 'Permintaan memuat field yang tidak didukung.',
                'errors' => [
                    'request' => ['Hapus field yang tidak didukung lalu coba lagi.'],
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'uuid'],
        ], [
            'message.required' => 'Pesan wajib diisi.',
            'message.string' => 'Pesan wajib berupa teks.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
            'conversation_id.uuid' => 'ID percakapan tidak valid.',
        ]);

        $conversation = null;

        if (filled($validated['conversation_id'] ?? null)) {
            $conversation = ChatConversation::query()
                ->where('uuid', $validated['conversation_id'])
                ->first();

            if ($conversation === null || ! $this->ownsConversation($conversation, $request)) {
                return response()->json([
                    'message' => 'Percakapan tidak ditemukan.',
                    'code' => 'conversation_not_found',
                ], Response::HTTP_NOT_FOUND);
            }
        }

        $history = $conversation === null
            ? collect()
            : $conversation->messages()
                ->latest('id')
                ->limit(20)
                ->get()
                ->sortBy('id')
                ->values();

        try {
            $reply = $this->chatbotService->reply($validated['message'], $history);
        } catch (ChatbotUnavailableException) {
            return response()->json([
                'message' => 'Asisten sedang tidak tersedia. Silakan coba lagi sebentar.',
                'code' => 'chatbot_unavailable',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $conversation = DB::transaction(function () use ($conversation, $request, $validated, $reply): ChatConversation {
                if ($conversation === null) {
                    $conversation = ChatConversation::query()->create([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $request->user()?->id,
                        'session_hash' => $request->user() === null ? $this->sessionHash($request) : null,
                    ]);
                }

                $conversation->messages()->create([
                    'role' => 'user',
                    'content' => $validated['message'],
                ]);

                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $reply['message'],
                ]);

                $conversation->touch();

                return $conversation;
            });
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Percakapan tidak dapat disimpan saat ini. Silakan coba lagi.',
                'code' => 'conversation_storage_unavailable',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $request->session()->put('mihom_chat_conversation_id', $conversation->uuid);

        return response()->json([
            'conversation_id' => $conversation->uuid,
            'message' => $reply['message'],
            'properties' => $reply['properties']
                ->map(fn (Property $property): array => [
                    'id' => $property->uuid,
                    'title' => $property->title,
                    'price' => (float) $property->price,
                    'location' => collect([$property->district, $property->city, $property->province])
                        ->filter()
                        ->implode(', '),
                    'bedrooms' => $property->rooms,
                    'url' => route('property-details', $property->uuid),
                ])
                ->values(),
        ]);
    }

    private function ownsConversation(ChatConversation $conversation, Request $request): bool
    {
        $user = $request->user();

        if ($user !== null) {
            return $conversation->user_id === $user->id;
        }

        return $conversation->user_id === null
            && is_string($conversation->session_hash)
            && hash_equals($conversation->session_hash, $this->sessionHash($request));
    }

    private function sessionHash(Request $request): string
    {
        return hash('sha256', $request->session()->getId());
    }
}
