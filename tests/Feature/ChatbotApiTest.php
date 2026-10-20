<?php

use App\Models\ChatConversation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createChatProperty(string $title, string $status, string $city, string $price, string $rooms): Property
{
    return Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => User::factory()->create()->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => $status,
        'title' => $title,
        'description' => 'Listing properti untuk pengujian.',
        'price' => $price,
        'negotiable' => false,
        'province' => 'Jawa Tengah',
        'city' => $city,
        'district' => 'Purwokerto Timur',
        'address' => 'Jalan Uji No. 1',
        'land_area_sqm' => 100,
        'building_area_sqm' => 80,
        'rooms' => $rooms,
        'certificate_type' => 'SHM',
    ]);
}

it('returns only published matching properties and stores the conversation', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Saya menemukan satu rumah yang sesuai.']],
            ],
        ]),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $matchingProperty = createChatProperty('Rumah Banyumas 3 Kamar', 'published', 'Banyumas', '450000000', '3');
    createChatProperty('Rumah belum disetujui', 'pending_verification', 'Banyumas', '400000000', '3');
    createChatProperty('Rumah di atas anggaran', 'published', 'Banyumas', '600000000', '3');
    createChatProperty('Rumah di kota lain', 'published', 'Bandung', '400000000', '3');

    $response = $this->postJson(route('chatbot.reply'), [
        'message' => 'Cari rumah di Banyumas di bawah 500 juta dengan 3 kamar tidur',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Saya menemukan satu rumah yang sesuai.')
        ->assertJsonPath('properties.0.id', $matchingProperty->uuid)
        ->assertJsonPath('properties.0.title', 'Rumah Banyumas 3 Kamar');

    $this->assertDatabaseCount('chat_conversations', 1);
    $this->assertDatabaseCount('chat_messages', 2);
    $this->assertDatabaseHas('chat_conversations', [
        'uuid' => $response->json('conversation_id'),
        'user_id' => null,
    ]);

    Http::assertSent(fn (Request $request): bool => str_contains(
        $request['messages'][1]['content'],
        'Rumah Banyumas 3 Kamar',
    ) && ! str_contains($request['messages'][1]['content'], 'Rumah belum disetujui'));
});

it('uses saved context to filter follow-up property questions', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => 'Ada beberapa pilihan.']]]])
            ->push(['choices' => [['message' => ['content' => 'Ini pilihan dengan tiga kamar.']]]]),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $matchingProperty = createChatProperty('Rumah Banyumas 3 Kamar', 'published', 'Banyumas', '450000000', '3');
    createChatProperty('Rumah Banyumas 2 Kamar', 'published', 'Banyumas', '400000000', '2');

    $firstResponse = $this->postJson(route('chatbot.reply'), [
        'message' => 'Cari rumah di Banyumas di bawah 500 juta',
    ])->assertOk();
    $sessionCookie = $firstResponse->getCookie(config('session.cookie'), false)->getValue();
    $this->assertDatabaseHas('chat_conversations', [
        'uuid' => $firstResponse->json('conversation_id'),
        'session_hash' => hash(
            'sha256',
            $firstResponse->getCookie(config('session.cookie'))->getValue(),
        ),
    ]);
    $this->withCredentials()
        ->withUnencryptedCookie(config('session.cookie'), $sessionCookie)
        ->get(route('home'))
        ->assertSee('data-conversation-id="'.$firstResponse->json('conversation_id').'"', false);

    $secondResponse = $this->withCredentials()->withUnencryptedCookie(
        config('session.cookie'),
        $sessionCookie,
    )->postJson(route('chatbot.reply'), [
        'conversation_id' => $firstResponse->json('conversation_id'),
        'message' => 'Yang punya tiga kamar tidur',
    ]);
    $this->assertSame(
        $firstResponse->getCookie(config('session.cookie'))->getValue(),
        $secondResponse->getCookie(config('session.cookie'))->getValue(),
    );

    $secondResponse->assertOk()
        ->assertJsonPath('properties.0.id', $matchingProperty->uuid)
        ->assertJsonCount(1, 'properties');

    Http::assertSent(fn (Request $request): bool => collect($request['messages'])->contains(
        fn (array $message): bool => $message['role'] === 'user'
            && $message['content'] === 'Cari rumah di Banyumas di bawah 500 juta',
    ));
});

it('returns 422 for invalid or unexpected input', function () {
    Http::preventStrayRequests();
    Http::fake();

    $this->postJson(route('chatbot.reply'), [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.message.0', 'Pesan wajib diisi.');

    $this->postJson(route('chatbot.reply'), [
        'message' => 'Cari rumah',
        'unexpected' => 'value',
    ])->assertUnprocessable()
        ->assertJsonPath('errors.request.0', 'Hapus field yang tidak didukung lalu coba lagi.');
    $this->postJson(route('chatbot.reply'), [
        'message' => str_repeat('a', 2001),
    ])->assertUnprocessable()
        ->assertJsonPath('errors.message.0', 'Pesan maksimal 2000 karakter.');

    Http::assertNothingSent();
    $this->assertDatabaseCount('chat_conversations', 0);
});

it('returns 404 when a different user requests an existing conversation', function () {
    Http::preventStrayRequests();
    Http::fake();
    $owner = User::factory()->create();
    $conversation = ChatConversation::query()->create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $owner->id,
    ]);
    $conversation->messages()->create([
        'role' => 'user',
        'content' => 'Pesan pribadi pemilik percakapan.',
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('chatbot.reply'), [
            'conversation_id' => $conversation->uuid,
            'message' => 'Tampilkan riwayat percakapan itu',
        ]);

    $response->assertNotFound()
        ->assertJsonPath('code', 'conversation_not_found');

    Http::assertNothingSent();
    $this->assertDatabaseCount('chat_messages', 1);
});

it('returns 404 when another browser session requests a guest conversation', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Saya akan membantu mencari properti.']]],
        ]),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $firstResponse = $this->postJson(route('chatbot.reply'), [
        'message' => 'Cari properti di Banyumas',
    ])->assertOk();

    $secondResponse = $this->postJson(route('chatbot.reply'), [
        'conversation_id' => $firstResponse->json('conversation_id'),
        'message' => 'Tampilkan percakapan sebelumnya',
    ]);

    $secondResponse->assertNotFound()
        ->assertJsonPath('code', 'conversation_not_found');

    Http::assertSentCount(1);
    $this->assertDatabaseCount('chat_messages', 2);
});

it('returns 503 and does not store a conversation when the provider is not configured', function () {
    Http::preventStrayRequests();
    Http::fake();
    config(['services.groq.api_key' => null]);

    $this->postJson(route('chatbot.reply'), [
        'message' => 'Cari rumah di Banyumas',
    ])->assertServiceUnavailable()
        ->assertJsonPath('code', 'chatbot_unavailable')
        ->assertJsonMissingPath('trace');

    Http::assertNothingSent();
    $this->assertDatabaseCount('chat_conversations', 0);
    $this->assertDatabaseCount('chat_messages', 0);
});

it('returns 503 without exposing upstream errors when the provider fails', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'error' => ['message' => 'private upstream diagnostic'],
        ], 500),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $this->postJson(route('chatbot.reply'), [
        'message' => 'Cari rumah di Banyumas',
    ])->assertServiceUnavailable()
        ->assertJsonPath('code', 'chatbot_unavailable')
        ->assertJsonMissing(['private upstream diagnostic']);

    $this->assertDatabaseCount('chat_conversations', 0);
    $this->assertDatabaseCount('chat_messages', 0);
});

it('returns 429 after ten chatbot requests in one minute from the same IP', function () {
    Http::preventStrayRequests();
    Http::fake();

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->postJson(route('chatbot.reply'), [])
            ->assertUnprocessable();
    }

    $this->postJson(route('chatbot.reply'), [])
        ->assertTooManyRequests();
});
