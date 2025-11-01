<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OcrController extends Controller
{
    public function showUploadForm()
    {
        return view('upload');
    }

    public function processUpload(Request $request)
    {
        $request->validate([
            'file_surat' => 'required|image|mimes:jpeg,png,jpg,gif,bmp|max:5120', // Maks 5MB
        ]);

        try {
            // 1. Inisialisasi Guzzle Client
            $client = new Client();

            // 2. Ambil file dari request
            $file = $request->file('file_surat');

            // 3. Tentukan URL API Python Anda
            // Pastikan URL ini benar (ganti jika API Anda di server lain)
            $ocrApiUrl = 'http://localhost:8000/ocr';

            // 4. Kirim request POST dengan 'multipart/form-data'
            $response = $client->request('POST', $ocrApiUrl, [
                'multipart' => [
                    [
                        'name'     => 'file', // Nama field 'file' (sesuai di FastAPI)
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName()
                    ]
                ]
            ]);

            // 5. Ambil hasil dari API
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);

            // 6. Cek jika sukses dan kirim data ke view
            if ($result['status'] == 'success') {
                // $result['result_text'] akan berisi array teks
                // Anda bisa olah data ini (misal: simpan ke DB)
                // sebelum menampilkannya
                return redirect()->route('surat.upload.form')
                    ->with('ocr_results', $result['result_text']);
            } else {
                return back()->with('error', 'Gagal memproses OCR: ' . $result['message']);
            }

        } catch (\Exception $e) {
            // Tangani jika API Python mati atau ada error jaringan
            return back()->with('error', 'Gagal terhubung ke service OCR: ' . $e->getMessage());
        }
    }
}
