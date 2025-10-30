<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process; // Menggunakan Symfony Process untuk menjalankan perintah
use Exception;

class OcrController extends Controller
{
    /**
     * Memproses file GAMBAR yang diunggah untuk OCR menggunakan skrip EasyOCR Python.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        // Validasi hanya untuk gambar, karena PDF diubah di frontend.
        $request->validate([
            'ocr_file' => 'required|image|max:5120', // Maksimal 5MB
        ]);

        try {
            $file = $request->file('ocr_file');
            $imagePath = $file->getRealPath(); // Path sementara file yang diunggah

            // Path ke interpreter Python (biasanya 'python' jika sudah di PATH)
            $pythonExecutable = 'python';
            // Path ke skrip Python kita (di direktori utama Laravel)
            $scriptPath = base_path('ocr_processor.py');

            // Membuat perintah untuk dijalankan: python ocr_processor.py /path/ke/gambar.png
            $process = new Process([$pythonExecutable, $scriptPath, $imagePath]);
            $process->run();

            // Cek apakah skrip Python berhasil dijalankan
            if (!$process->isSuccessful()) {
                // Jika gagal, lempar exception dengan output error dari Python
                throw new ProcessFailedException($process);
            }

            // Ambil output JSON dari skrip Python
            $output = json_decode($process->getOutput(), true);

            // Periksa output JSON
            if (isset($output['success']) && $output['success']) {
                // Jika sukses, kembalikan teks hasil OCR
                return response()->json(['text' => $output['text']]);
            } else {
                // Jika skrip Python mengembalikan error, teruskan pesan errornya
                $errorMessage = $output['error'] ?? 'Skrip Python OCR gagal tanpa pesan error.';
                return response()->json(['error' => $errorMessage], 500);
            }

        } catch (ProcessFailedException $exception) {
            // Error jika perintah Python itu sendiri gagal dijalankan
            // (misal: Python tidak ditemukan, skrip tidak ada, dll.)
            return response()->json(['error' => 'Gagal menjalankan proses OCR Python: ' . $exception->getErrorOutput()], 500);
        } catch (Exception $e) {
            // Mencatat error lengkap ke file log Laravel
            \Illuminate\Support\Facades\Log::error('OCR Scan Failed: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString() // Menambahkan trace lengkap
            ]);

            // Tetap kirim pesan error umum ke pengguna
            return response()->json(['error' => 'Terjadi masalah pada server. Silakan cek log aplikasi.'], 500);
            // --- AKHIR PERUBAHAN ---
        }
    }
}
