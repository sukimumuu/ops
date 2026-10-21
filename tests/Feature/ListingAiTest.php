<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function fakeGroqMessage(string $content): array
{
    return [
        'choices' => [
            ['message' => ['content' => $content]],
        ],
    ];
}

it('extracts listing data from unstructured text and normalizes it', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response(fakeGroqMessage(json_encode([
            'judul' => 'Rumah 2 Lantai di Bintaro',
            'harga' => '1,8 miliar',
            'tipe_properti' => 'Building',
            'jenis_bangunan' => 'Rumah',
            'tujuan_transaksi' => 'For Sale',
            'luas_tanah_m2' => '150 meter persegi',
            'luas_bangunan_m2' => '120 m2',
            'kamar_tidur' => '3',
            'kamar_mandi' => 2,
            'jumlah_lantai' => 2,
            'sertifikat' => 'shm',
            'bisa_nego' => true,
            'deskripsi' => 'Deskripsi yang tidak boleh menimpa kolom deskripsi.',
            'field_tidak_dikenal' => 'abaikan',
        ], JSON_UNESCAPED_UNICODE))),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.extract'), [
            'text' => 'Dijual rumah 2 lantai di Bintaro, luas bangunan 120 meter persegi, luas tanah 150 meter persegi, harga 1,8 miliar, terdiri dari 3 kamar tidur dan 2 kamar mandi.',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.title', 'Rumah 2 Lantai di Bintaro')
        ->assertJsonPath('data.price', 1800000000)
        ->assertJsonPath('data.property_type', 'bangunan')
        ->assertJsonPath('data.building_type', 'rumah')
        ->assertJsonPath('data.transaction_type', 'dijual')
        ->assertJsonPath('data.land_area_sqm', 150)
        ->assertJsonPath('data.building_area_sqm', 120)
        ->assertJsonPath('data.bedrooms', 3)
        ->assertJsonPath('data.bathrooms', 2)
        ->assertJsonPath('data.floors', 2)
        ->assertJsonPath('data.certificate_type', 'SHM')
        ->assertJsonPath('data.negotiable', true)
        ->assertJsonMissingPath('data.description');

    expect($response->json('fields'))->toContain('title', 'price', 'property_type');

    Http::assertSent(function (Request $request): bool {
        return $request['response_format'] === ['type' => 'json_object']
            && collect($request['messages'])->contains(
                fn (array $message): bool => str_contains($message['content'], '1,8 miliar'),
            );
    });
});

it('drops unsupported categorical values while keeping valid fields', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response(fakeGroqMessage(json_encode([
            'judul' => 'Rumah Bintaro',
            'harga' => 'Rp 1.800.000.000',
            'tipe_properti' => 'bangunan',
            'kondisi' => 'rusak berat',
            'sertifikat' => 'HGB',
        ], JSON_UNESCAPED_UNICODE))),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.extract'), [
            'text' => 'Dijual rumah di Bintaro, harga 1,8 miliar, sertifikat HGB.',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Rumah Bintaro')
        ->assertJsonPath('data.price', 1800000000)
        ->assertJsonPath('data.certificate_type', 'SHGB')
        ->assertJsonMissingPath('data.condition');
});

it('requires authentication before using the listing AI endpoints', function () {
    $this->postJson(route('listing.ai.extract'), ['text' => 'Dijual rumah di Bintaro.'])
        ->assertUnauthorized();

    $this->postJson(route('listing.ai.description'), ['title' => 'Rumah Bintaro'])
        ->assertUnauthorized();
});

it('returns a safe validation error when the provider response is not valid JSON', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response(
            fakeGroqMessage('Maaf, saya tidak dapat membaca data tersebut.'),
        ),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.extract'), [
            'text' => 'Dijual rumah di Bintaro dengan harga menarik.',
        ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'no_property_data')
        ->assertJsonMissingPath('data');
});

it('retries without JSON mode when the provider rejects the response format', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::sequence()
            ->push(['error' => ['code' => 'invalid_request_error']], 400)
            ->push(fakeGroqMessage(json_encode([
                'judul' => 'Rumah Bintaro',
                'harga' => 1800000000,
            ], JSON_UNESCAPED_UNICODE)), 200),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.extract'), [
            'text' => 'Dijual rumah di Bintaro dengan harga 1,8 miliar.',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Rumah Bintaro')
        ->assertJsonPath('data.price', 1800000000);

    Http::assertSentCount(2);
});

it('returns a service unavailable response when the provider fails', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'error' => ['code' => 'rate_limit_exceeded'],
        ], 429),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.extract'), [
            'text' => 'Dijual rumah di Bintaro dengan harga 1 miliar.',
        ])
        ->assertStatus(503)
        ->assertJsonPath('code', 'ai_unavailable')
        ->assertJsonPath('message', 'Layanan AI sedang tidak tersedia. Silakan coba lagi sebentar.');
});

it('validates the extraction input', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.extract'), ['text' => 'pendek'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('text');
});

it('generates a property description from the latest form values', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response(fakeGroqMessage(
            "```\nDeskripsi properti: Rumah dua lantai di Bintaro ini menawarkan ruang keluarga yang lapang.\n```",
        )),
    ]);
    config(['services.groq.api_key' => 'test-api-key']);

    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.description'), [
            'property_type' => 'bangunan',
            'building_type' => 'rumah',
            'transaction_type' => 'dijual',
            'title' => 'Rumah 2 Lantai di Bintaro',
            'price' => 1800000000,
            'land_area_sqm' => 150,
            'building_area_sqm' => 120,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 2,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath(
            'description',
            'Rumah dua lantai di Bintaro ini menawarkan ruang keluarga yang lapang.',
        );

    Http::assertSent(function (Request $request): bool {
        $prompt = collect($request['messages'])->pluck('content')->implode("\n");

        return str_contains($prompt, 'Rumah 2 Lantai di Bintaro')
            && str_contains($prompt, 'Rp 1.800.000.000')
            && str_contains($prompt, 'Jenis bangunan: rumah');
    });
});

it('requires at least one useful field before generating a description', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.ai.description'), [
            'province' => 'Banten',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('form');
});

it('renders both AI actions on the listing form', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('listing.create'))
        ->assertOk()
        ->assertSee('Isi data iklan dalam hitungan detik!')
        ->assertSee('Gunakan AI')
        ->assertSee('Isi Form dengan AI')
        ->assertSee('Buat deskripsi iklan dengan AI');
});
