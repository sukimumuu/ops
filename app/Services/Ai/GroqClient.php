<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Small shared client for Groq chat completions.
 *
 * The API key and base URL stay on the server. Callers only receive the
 * assistant message array and never see provider errors or credentials.
 */
class GroqClient
{
    /**
     * Send a chat completion request and return the first assistant message.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws AiProviderException
     */
    public function chat(array $payload): array
    {
        $apiKey = config('services.groq.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            Log::warning('AI request rejected because the provider is not configured.');

            throw new AiProviderException('AI provider is not configured.');
        }

        $payload['model'] ??= config('services.groq.chat_model');

        try {
            $response = Http::connectTimeout(5)
                ->timeout(25)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', $payload);
        } catch (ConnectionException) {
            Log::warning('AI request could not connect to the provider.');

            throw new AiProviderException('AI provider is unavailable.');
        }

        if (! $response->successful()) {
            Log::warning('AI provider returned an unsuccessful response.', [
                'status' => $response->status(),
                'error_code' => $response->json('error.code'),
            ]);

            throw new AiProviderException('AI provider returned an error.', $response->status());
        }

        $assistantMessage = $response->json('choices.0.message');

        if (! is_array($assistantMessage)) {
            Log::warning('AI provider returned an invalid response.');

            throw new AiProviderException('AI provider returned an invalid response.');
        }

        return $assistantMessage;
    }
}
