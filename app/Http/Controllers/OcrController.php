<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Imagick;
use Exception; // Tambahkan ini

class OcrController extends Controller
{
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'ocr_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        try {
            $apiKey = 'K85714586788957';
            if ($apiKey === 'YOUR_API_KEY_HERE') {
                return response()->json(['error' => 'API Key OCR.space belum diatur.'], 500);
            }

            $file = $request->file('ocr_file');
            $fileContent = file_get_contents($file->getRealPath());
            $fileName = $file->getClientOriginalName();

            if ($file->getMimeType() === 'application/pdf') {
                $imagick = new Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($file->getRealPath() . '[0]');
                $imagick->setImageFormat('png');

                $fileContent = $imagick->getImageBlob();
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.png';
                $imagick->clear();
                $imagick->destroy();
            }

            $response = Http::withHeaders(['apikey' => $apiKey])
                ->attach('file', $fileContent, $fileName)
                ->post('https://api.ocr.space/parse/image', [
                    'language' => 'ind',
                    'isOverlayRequired' => 'false',
                    'OCREngine' => '1',
                ]);

            $result = $response->json();

            if (isset($result['ParsedResults'][0]['ParsedText'])) {
                return response()->json(['text' => $result['ParsedResults'][0]['ParsedText']]);
            } else {
                $errorMessage = $result['ErrorMessage'][0] ?? 'Gagal memproses dokumen.';
                return response()->json(['error' => $errorMessage], 400);
            }

        } catch (Exception $e) {
            // --- PERUBAHAN DI SINI ---
            // Kode ini akan memberikan pesan error yang jauh lebih detail
            $detailedError = sprintf(
                "Imagick Error in %s on line %d: %s",
                basename($e->getFile()), // Hanya nama file, bukan path lengkap
                $e->getLine(),
                $e->getMessage()
            );
            return response()->json(['error' => $detailedError], 500);
            // --- AKHIR PERUBAHAN ---
        }
    }
}

