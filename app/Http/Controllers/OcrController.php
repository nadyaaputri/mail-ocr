<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Storage\StorageClient;
use Exception;

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
        // #1. Validasi Diperbarui: Sekarang menerima gambar DAN pdf.
        $request->validate([
            'ocr_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240', // Maksimal 10MB
        ]);

        try {
            $credentialsPath = storage_path('app/google-credentials.json');
            if (!file_exists($credentialsPath)) {
                return response()->json(['error' => 'File kredensial Google tidak ditemukan.'], 500);
            }

            $file = $request->file('ocr_file');
            $mimeType = $file->getMimeType();
            $fullText = '';

            $imageAnnotator = new ImageAnnotatorClient(['credentials' => $credentialsPath]);

            // #2. Logika Percabangan: Cek apakah file adalah gambar atau PDF.
            if (in_array($mimeType, ['image/jpeg', 'image/png'])) {
                // --- PROSES UNTUK GAMBAR ---
                $imageContent = file_get_contents($file->getRealPath());
                $response = $imageAnnotator->textDetection($imageContent);
                $texts = $response->getTextAnnotations();
                if ($texts) {
                    $fullText = $texts[0]->getDescription();
                }
            } elseif ($mimeType === 'application/pdf') {
                // --- LOGIKA BARU UNTUK PDF ---
                $fullText = $this->processPdf($file, $credentialsPath);
            }

            return response()->json(['text' => $fullText]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal memproses OCR: ' . $e->getMessage()], 500);
        } finally {
            if (isset($imageAnnotator)) {
                $imageAnnotator->close();
            }
        }
    }

    /**
     * Menangani proses OCR khusus untuk file PDF.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $credentialsPath
     * @return string
     * @throws \Exception
     */
    private function processPdf($file, string $credentialsPath): string
    {
        // GANTI DENGAN NAMA BUCKET GOOGLE CLOUD STORAGE ANDA
        $bucketName = 'ganti-dengan-nama-bucket-anda';

        // 1. Buat klien Google Cloud Storage.
        $storage = new StorageClient(['keyFilePath' => $credentialsPath]);
        $bucket = $storage->bucket($bucketName);

        // 2. Unggah file PDF ke bucket.
        $fileName = 'ocr-uploads/' . uniqid() . '-' . $file->getClientOriginalName();
        $object = $bucket->upload(
            fopen($file->getRealPath(), 'r'),
            ['name' => $fileName]
        );

        // 3. Jalankan proses OCR asinkron pada file di bucket.
        $imageAnnotator = new ImageAnnotatorClient(['credentials' => $credentialsPath]);
        $gcsUri = "gs://{$bucketName}/{$fileName}";
        $outputUri = "gs://{$bucketName}/ocr-outputs/" . uniqid() . '/';

        $operation = $imageAnnotator->asyncBatchAnnotateFiles([
            'requests' => [
                [
                    'input_config' => ['gcs_source' => ['uri' => $gcsUri], 'mime_type' => 'application/pdf'],
                    'features' => [['type' => \Google\Cloud\Vision\V1\Feature\Type::DOCUMENT_TEXT_DETECTION]],
                    'output_config' => ['gcs_destination' => ['uri' => $outputUri], 'batch_size' => 1],
                ]
            ]
        ]);

        // 4. Tunggu proses OCR selesai.
        $operation->pollUntilComplete();

        // 5. Baca hasil dari file JSON yang dibuat oleh Vision API.
        $fullText = '';
        $outputPrefix = str_replace("gs://{$bucketName}/", '', $outputUri);
        $resultObjects = $bucket->objects(['prefix' => $outputPrefix]);

        foreach ($resultObjects as $resultObject) {
            $jsonString = $resultObject->downloadAsString();
            $data = json_decode($jsonString, true);
            foreach ($data['responses'] as $response) {
                if (isset($response['fullTextAnnotation']['text'])) {
                    $fullText .= $response['fullTextAnnotation']['text'];
                }
            }
        }

        // 6. Hapus file sementara dari bucket untuk menghemat ruang.
        $object->delete();
        foreach ($bucket->objects(['prefix' => $outputPrefix]) as $resultObject) {
            $resultObject->delete();
        }

        $imageAnnotator->close();

        return $fullText;
    }
}

