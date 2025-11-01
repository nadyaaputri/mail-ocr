import uvicorn
from fastapi import FastAPI, UploadFile, File
from paddleocr import PaddleOCR
import numpy as np
import cv2
from fastapi.middleware.cors import CORSMiddleware

# Inisialisasi aplikasi FastAPI
app = FastAPI(title="PaddleOCR API Service")

# Tentukan domain Laravel Anda
origins = [
    "http://localhost:8000",
    "http://127.0.0.1:8000",
    "http://localhost", # Jika Anda pakai virtual host
]
app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Muat model PaddleOCR saat aplikasi dimulai
# 'id' untuk Bahasa Indonesia, 'en' untuk Inggris. Bisa keduanya: ['id', 'en']
print("Memuat model PaddleOCR...")
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
        # Baca file gambar yang di-upload
        contents = await file.read()

        # Konversi gambar ke format yang bisa dibaca OpenCV/PaddleOCR
        nparr = np.fromstring(contents, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        # Jalankan proses OCR
        result = ocr.ocr(img, cls=True)

        # Ekstrak hanya teks dari hasil
        # Hasil mentah 'result' berisi [bounding_box, (text, confidence_score)]
        # Kita ambil teksnya saja.

        extracted_text = []
        if result and result[0] is not None:
            for line in result[0]:
                extracted_text.append(line[1][0]) # Ambil teksnya saja

        return {
            "status": "success",
            "filename": file.filename,
            "result_text": extracted_text # Mengembalikan array berisi baris-baris teks
        }

    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    # Jalankan server API
    uvicorn.run(app, host="0.0.0.0", port=8000)
