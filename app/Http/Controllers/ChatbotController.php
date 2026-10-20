<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// Pastikan Anda mengimpor Model Properti yang Anda gunakan di Mihom
use App\Models\Property; 

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $userMessage = $request->input('message');

        // 1. Ambil data properti dari database (Contoh: 5 properti terbaru yang disetujui)
        // Sesuaikan nama kolom ('status', 'is_approved') dengan struktur tabel Mihom Anda
        $properties = Property::where('status', 'Dijual')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['judul', 'harga', 'lokasi', 'kamar_tidur', 'kamar_mandi', 'luas_tanah']);

        // 2. Format data properti menjadi string agar bisa dibaca oleh AI
        $contextData = "Berikut adalah daftar properti yang tersedia di Mihom saat ini:\n";
        foreach ($properties as $prop) {
            // Format angka harga agar rapi (asumsi harga dalam integer)
            $harga = 'Rp ' . number_format($prop->harga, 0, ',', '.');
            $contextData .= "- {$prop->judul} di {$prop->lokasi}. Harga: {$harga}. Spesifikasi: {$prop->kamar_tidur} KT, {$prop->kamar_mandi} KM, Luas Tanah {$prop->luas_tanah} m2.\n";
        }

        // 3. Susun System Prompt yang kuat
        $systemPrompt = "Kamu adalah asisten agen properti resmi untuk platform Mihom. 
Tugasmu adalah membantu pelanggan mencari rumah yang cocok, menjawab dengan ramah, profesional, dan menggunakan bahasa Indonesia yang natural.
JANGAN PERNAH merekomendasikan properti yang tidak ada dalam daftar di bawah ini. Jika properti yang dicari tidak ada, katakan dengan sopan bahwa saat ini belum tersedia.

Data Properti:
" . $contextData;

        // 4. Kirim Request ke Groq API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ],
            'temperature' => 0.5, // Diturunkan sedikit agar AI tidak terlalu berhalusinasi dan lebih fokus pada data
        ]);

        if ($response->successful()) {
            $reply = $response->json('choices.0.message.content');
            return response()->json(['message' => $reply]);
        }

        return response()->json([
            'error' => 'Gagal menghubungi server AI. Silakan coba lagi nanti.'
        ], 500);
    }
}