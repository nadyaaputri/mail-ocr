import uvicorn
from fastapi import FastAPI, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from paddleocr import PaddleOCR
import numpy as np
import cv2

# Inisialisasi aplikasi FastAPI
app = FastAPI(title="PaddleOCR API Service")

# --- Konfigurasi CORS ---
origins = [
    "http://localhost:8000",
    "http://127.0.0.1:8000",
]
app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- Muat Model OCR ---
print("Memuat model PaddleOCR...")
# Pastikan bahasa 'id' (Indonesia) sudah di-instal
ocr = PaddleOCR(use_angle_cls=True, lang='id')
print("Model berhasil dimuat.")

@app.get("/")
def read_root():
    return {"message": "PaddleOCR API Service is running."}

@app.post("/ocr")
async def process_ocr(file: UploadFile = File(...)):
    """
    Endpoint untuk menerima gambar dan mengembalikan hasil OCR.
    """
    try:
        print(f"\n--- Menerima file: {file.filename} ---")
        contents = await file.read()

        nparr = np.frombuffer(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            print("!!! ERROR: cv2.imdecode gagal memuat gambar.")
            return {"status": "error", "message": "Gagal membaca file gambar. Pastikan file adalah JPG/PNG yang valid."}

        print("Mulai menjalankan ocr.ocr(img)...")
        result = ocr.ocr(img)
        print("Proses ocr.ocr(img) selesai.")

        # --- INI ADALAH PERBAIKANNYA ---
        extracted_text = []
        if result and result[0] is not None:
            # Kita langsung ambil dari kunci 'rec_texts'
            if 'rec_texts' in result[0]:
                extracted_text = result[0]['rec_texts']
                print(f"Teks yang diekstrak (BERHASIL): {extracted_text}")
            else:
                print("!!! ERROR: 'rec_texts' key not found in PaddleOCR result.")
        else:
            print("!!! ERROR: PaddleOCR returned an empty or invalid result.")
        # --- AKHIR PERBAIKAN ---

        return {
            "status": "success",
            "filename": file.filename,
            "result_text": extracted_text # Ini sekarang akan berisi teks yang benar
        }

    except Exception as e:
        print(f"!!! ERROR FATAL DI FUNGSI OCR: {str(e)}")
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    # Jalankan server API di port 8001
    uvicorn.run(app, host="0.0.0.0", port=8001)

