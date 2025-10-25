<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Exception;

class OcrController extends Controller
{
    /**
     * Memproses file GAMBAR yang diunggah untuk OCR menggunakan OCR.space API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        // Validasi sekarang hanya untuk gambar, karena PDF sudah diubah di frontend.
        $request->validate([
            'ocr_file' => 'required|image|max:5120', // Maksimal 5MB
        ]);

        try {
            // GANTI DENGAN API KEY ANDA DARI OCR.SPACE
            $apiKey = 'K85714586788957'; // API Key Anda

            if ($apiKey === 'YOUR_API_KEY_HERE') {
                return response()->json(['error' => 'API Key OCR.space belum diatur di OcrController.'], 500);
            }

            // Kirim file gambar ke OCR.space
            $response = Http::withHeaders([
                'apikey' => $apiKey,
            ])->attach(
                'file', file_get_contents($request->file('ocr_file')), $request->file('ocr_file')->getClientOriginalName()
            )->post('https://api.ocr.space/parse/image', [
                'language' => 'ind',           // Bahasa Indonesia
                'isOverlayRequired' => 'false',
                'OCREngine' => '1',             // Engine 1 adalah yang paling stabil
            ]);

            $result = $response->json();

            // Periksa apakah OCR berhasil
            if (isset($result['ParsedResults'][0]['ParsedText'])) {
                $fullText = $result['ParsedResults'][0]['ParsedText'];
                return response()->json(['text' => $fullText]);
            } else {
                $errorMessage = $result['ErrorMessage'][0] ?? 'Gagal memproses dokumen.';
                if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing']) {
                    $errorMessage = $result['ErrorDetails'] ?? $errorMessage;
                }
                return response()->json(['error' => $errorMessage], 400);
            }

        } catch (Exception $e) {
            return response()->json(['error' => 'Terjadi masalah pada server: ' . $e->getMessage()], 500);
        }
    }
}

