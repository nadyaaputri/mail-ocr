import os
# Memaksa Paddle untuk tidak menggunakan MKLDNN sama sekali
os.environ["FLAGS_use_mkldnn"] = "0"
os.environ["FLAGS_enable_mkldnn"] = "0"
os.environ["DN_USE_MKLDNN"] = "0"
import uvicorn
from fastapi import FastAPI, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from paddleocr import PaddleOCR
import numpy as np
import cv2
from pypdf import PdfReader
from pdf2image import convert_from_bytes
import io
import logging

# Konfigurasi Logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="SIPERS OCR Service")

# Konfigurasi CORS
origins = ["*"]
app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- Inisialisasi Model OCR ---
print("--- MEMUAT MODEL PADDLE OCR (SAFE MODE) ---")
try:
    ocr = PaddleOCR(
        lang='id',            # Bahasa Indonesia
        use_angle_cls=False,  # FALSE: Matikan rotasi (berat)
        show_log=True,        # Tampilkan log
        use_onnx=False,       # FALSE: Engine standar
        enable_mkldnn=False,  # FALSE: Matikan di parameter
        use_gpu=False         # FALSE: Pakai CPU
    )
    print("--- MODEL BERHASIL DIMUAT! ---")
except Exception as e:
    print(f"FATAL ERROR saat load model: {e}")

def convert_pdf_to_image(file_bytes):
    """Mengubah halaman pertama PDF menjadi gambar numpy array"""
    try:
        images = convert_from_bytes(file_bytes)
        if images:
            img = np.array(images[0])
            img = cv2.cvtColor(img, cv2.COLOR_RGB2BGR)
            return img
    except Exception as e:
        logger.warning(f"Gagal convert PDF pakai pdf2image: {e}")
        return None

@app.post("/ocr")
async def ocr_process(file: UploadFile = File(...)):
    print(f"Menerima file: {file.filename}")

    try:
        contents = await file.read()
        image = None

        # 1. Cek Tipe File
        if file.filename.lower().endswith('.pdf'):
            print("Deteksi PDF, converting...")
            image = convert_from_bytes(contents)[0] # Ambil hal 1
            image = np.array(image)
            image = cv2.cvtColor(image, cv2.COLOR_RGB2BGR)
        else:
            # Gambar Biasa
            nparr = np.frombuffer(contents, np.uint8)
            image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if image is None:
            return {"status": "error", "message": "File tidak terbaca"}

        # 2. Lakukan OCR
        print("Mulai proses OCR (Safe Mode)...")
        # cls=False penting agar tidak memanggil model rotasi yang sering error
        result = ocr.ocr(image, cls=False)

        # 3. Ekstrak Teks
        extracted_text_list = []
        full_text = ""

        if result and result[0]:
            for line in result[0]:
                text = line[1][0]
                extracted_text_list.append(text)
                full_text += text + " "

        print("OCR Selesai!")

        return {
            "status": "success",
            "filename": file.filename,
            "result_text": extracted_text_list,
            "full_text": full_text
        }

    except Exception as e:
        print(f"ERROR: {e}")
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)
