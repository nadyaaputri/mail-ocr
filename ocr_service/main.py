import os

# --- KONFIGURASI SYSTEM ---
os.environ["KMP_DUPLICATE_LIB_OK"] = "TRUE"
os.environ["FLAGS_allocator_strategy"] = "naive_best_fit"
os.environ["FLAGS_fraction_of_gpu_memory_to_use"] = "0"
os.environ["FLAGS_enable_mkldnn"] = "0"
os.environ["FLAGS_use_mkldnn"] = "0"

import logging
import uvicorn
from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
import numpy as np
import cv2
from pdf2image import convert_from_bytes
from paddleocr import PaddleOCR

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
print(" MEMUAT PADDLE OCR (COMPATIBILITY MODE)...")
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
async def ocr_process(file: UploadFile = File(...)):
    if ocr_engine is None:
        return {"status": "error", "message": "Model Gagal Dimuat."}

    print(f"Processing File: {file.filename}")
    try:
        contents = await file.read()
        image = None

        # 1. Konversi PDF/Gambar
        if file.filename.lower().endswith('.pdf'):
            try:
                # GANTI PATH POPPLER JIKA PERLU
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

        # Inisialisasi variabel di luar loop (Wajib!)
        extracted_text_list = []
        confidence_scores = []

        # 3. Parsing Hasil
        if result and result[0]:
            for line in result[0]:
                text = line[1][0]
                score = line[1][1]

                extracted_text_list.append(text)
                confidence_scores.append(score)

        # 4. Hitung Akurasi
        avg_accuracy = 0.0
        if len(confidence_scores) > 0:
            avg_accuracy = sum(confidence_scores) / len(confidence_scores)

        accuracy_string = f"{avg_accuracy * 100:.2f}%"
        raw_text_formatted = "\n".join(extracted_text_list)

        print(f"Selesai! Akurasi: {accuracy_string}")

        return {
            "status": "success",
            "filename": file.filename,
            "raw_text": raw_text_formatted,

            # --- (Kirim Dua-duanya) ---
            "lines": extracted_text_list,         # Untuk Script Baru
            "result_text": extracted_text_list,   # Untuk Script Lama (Create.blade.php)

            "accuracy": accuracy_string
        }

    except Exception as e:
        logger.error(f"ERROR: {str(e)}")
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)
