import os
# --- KONFIGURASI SYSTEM ---
os.environ["KMP_DUPLICATE_LIB_OK"] = "TRUE"
os.environ["FLAGS_allocator_strategy"] = "naive_best_fit"
os.environ["FLAGS_fraction_of_gpu_memory_to_use"] = "0"
os.environ["FLAGS_enable_mkldnn"] = "0"
os.environ["FLAGS_use_mkldnn"] = "0"

import logging
import uvicorn
# Tambahkan 'Form' di sini untuk menerima input text biasa bersamaan dengan file
from fastapi import FastAPI, UploadFile, File, Form 
from fastapi.middleware.cors import CORSMiddleware
import numpy as np
import cv2
from pdf2image import convert_from_bytes
from paddleocr import PaddleOCR
import Levenshtein  # <--- WAJIB INSTALL: pip install python-Levenshtein

# Setup Logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("OCR_SERVICE")

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- GLOBAL VARIABLE ---
ocr_engine = None

print("\n" + "="*50)
print(" MEMUAT PADDLE OCR (COMPATIBILITY MODE + LEVENSHTEIN)...")
print("="*50 + "\n")

try:
    # Inisialisasi Model
    ocr_engine = PaddleOCR(
        lang='id',
        use_angle_cls=False
    )
    print("\n" + "="*50)
    print(" ✅ SUKSES! MODEL SIAP.")
    print("="*50 + "\n")

except Exception as e:
    print(f" ❌ ERROR LOAD MODEL: {e}")

@app.post("/ocr")
async def ocr_process(
    file: UploadFile = File(...),
    ground_truth: str = Form(None) # <--- Parameter Baru (Opsional)
):
    if ocr_engine is None:
        return {"status": "error", "message": "Model Gagal Dimuat."}

    print(f"Processing File: {file.filename}")
    try:
        contents = await file.read()
        image = None

        # 1. Konversi PDF/Gambar
        if file.filename.lower().endswith('.pdf'):
            try:
                # Path Poppler
                path_poppler = r"C:\poppler-25.12.0\Library\bin"
                images = convert_from_bytes(contents, poppler_path=path_poppler)
                if images:
                    image = cv2.cvtColor(np.array(images[0]), cv2.COLOR_RGB2BGR)
            except Exception as e:
                print(f"Error PDF: {e}")
                return {"status": "error", "message": "Gagal convert PDF. Cek Poppler."}
        else:
            nparr = np.frombuffer(contents, np.uint8)
            image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if image is None:
            return {"status": "error", "message": "File rusak/tidak terbaca"}

        # 2. Eksekusi OCR
        result = ocr_engine.ocr(image, cls=False)

        extracted_text_list = []
        confidence_scores = []

        # 3. Parsing Hasil
        if result and result[0]:
            for line in result[0]:
                text = line[1][0]
                score = line[1][1]

                extracted_text_list.append(text)
                confidence_scores.append(score)

        # Gabung hasil jadi satu string panjang
        raw_text_formatted = "\n".join(extracted_text_list)

        # ---------------------------------------------------------
        # 4. PERHITUNGAN METRIK (Confidence vs CER)
        # ---------------------------------------------------------
        
        # A. Confidence Score (Keyakinan AI)
        avg_confidence = 0.0
        if len(confidence_scores) > 0:
            avg_confidence = sum(confidence_scores) / len(confidence_scores)
        
        confidence_string = f"{avg_confidence * 100:.2f}%"

        # B. Real Accuracy via Levenshtein (Validasi Ilmiah)
        cer_score = 0
        real_accuracy_string = "N/A" # Default jika tidak ada Ground Truth

        if ground_truth:
            # Normalisasi: Lowercase & hapus spasi berlebih biar adil
            # Contoh: "Nomor :  123" dianggap sama dengan "nomor: 123"
            text_ocr_clean = " ".join(raw_text_formatted.split()).lower()
            text_gt_clean = " ".join(ground_truth.split()).lower()

            # Hitung Jarak (Berapa huruf yang beda)
            distance = Levenshtein.distance(text_ocr_clean, text_gt_clean)
            length = len(text_gt_clean)

            # Hitung CER (Error Rate)
            if length > 0:
                cer_score = distance / length
            else:
                cer_score = 1.0 # Error total jika GT kosong tapi hasil ada
            
            # Hitung Akurasi Asli (100% - Error Rate)
            real_accuracy_val = max(0, (1 - cer_score) * 100)
            real_accuracy_string = f"{real_accuracy_val:.2f}%"

        print(f"Selesai! Conf: {confidence_string} | Real Acc: {real_accuracy_string}")

        return {
            "status": "success",
            "filename": file.filename,
            "raw_text": raw_text_formatted,

            # Compatibility Mode (Kirim Dua-duanya)
            "lines": extracted_text_list,         
            "result_text": extracted_text_list,   

            # Data Statistik Lengkap
            "accuracy": confidence_string,      # Tetap kirim ini agar form surat tidak error
            "confidence": confidence_string,    # Nama alias yang lebih jelas
            "cer": f"{cer_score:.4f}",          # Tingkat Error
            "real_accuracy": real_accuracy_string # Akurasi Sebenarnya
        }

    except Exception as e:
        logger.error(f"ERROR: {str(e)}")
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)