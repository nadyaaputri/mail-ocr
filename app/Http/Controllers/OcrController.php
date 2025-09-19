<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class OcrController extends Controller
{
    /**
     * Memproses file yang diunggah untuk OCR dan mengembalikan teks yang terdeteksi.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        // 1. Validasi permintaan: Pastikan file ada dan merupakan gambar.
        $request->validate([
            'ocr_file' => 'required|image|max:5120', // Maksimal 5MB
        ]);

        try {
            // 2. Siapkan kredensial Google Cloud dari file JSON yang disimpan.
            $credentialsPath = storage_path('app/google-credentials.json');
            if (!file_exists($credentialsPath)) {
                return response()->json(['error' => 'File kredensial Google tidak ditemukan.'], 500);
            }

            // 3. Buat klien ImageAnnotator.
            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => $credentialsPath,
            ]);

            // 4. Ambil konten file gambar yang diunggah.
            $imageContent = file_get_contents($request->file('ocr_file')->getRealPath());

            // 5. Kirim permintaan deteksi teks ke Google Vision API.
            $response = $imageAnnotator->textDetection($imageContent);
            $texts = $response->getTextAnnotations();

            // 6. Periksa apakah ada teks yang terdeteksi.
            if ($texts) {
                // Ambil seluruh blok teks yang terdeteksi sebagai satu string.
                $fullText = $texts[0]->getDescription();

                // Kembalikan teks dalam format JSON.
                return response()->json(['text' => $fullText]);
            } else {
                return response()->json(['text' => '']); // Kembalikan string kosong jika tidak ada teks.
            }

        } catch (\Exception $e) {
            // Tangani error jika terjadi masalah saat menghubungi API.
            return response()->json(['error' => 'Gagal memproses OCR: ' . $e->getMessage()], 500);
        } finally {
            // Pastikan untuk menutup klien setelah selesai.
            if (isset($imageAnnotator)) {
                $imageAnnotator->close();
            }
        }
    }
}
