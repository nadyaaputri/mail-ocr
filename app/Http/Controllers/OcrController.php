<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use thiagoalessio\TesseractOCR\TesseractOCR; // Menggunakan library Tesseract
use Exception;

class OcrController extends Controller
{
    /**
     * Memproses file GAMBAR yang diunggah untuk OCR menggunakan Tesseract lokal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        // Validasi hanya untuk gambar, karena PDF sudah diubah di frontend.
        $request->validate([
            'ocr_file' => 'required|image|max:5120', // Maksimal 5MB
        ]);

        try {
            $file = $request->file('ocr_file');
            $imagePath = $file->getRealPath();

            // Jalankan Tesseract pada file gambar
            $text = (new TesseractOCR($imagePath))
                ->lang('ind') // Memberitahu Tesseract untuk menggunakan bahasa Indonesia
                ->run();

            return response()->json(['text' => $text]);

        } catch (Exception $e) {
            // Menangkap error jika Tesseract tidak terinstal atau ada masalah lain
            $errorMessage = 'Gagal memproses OCR: ' . $e->getMessage();
            // Cek apakah error karena Tesseract tidak ditemukan
            if (str_contains($e->getMessage(), 'Error executing command')) {
                $errorMessage = 'Gagal menjalankan Tesseract. Pastikan Tesseract sudah terinstal dan ditambahkan ke PATH sistem.';
            }
            return response()->json(['error' => $errorMessage], 500);
        }
    }
}

