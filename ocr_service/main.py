import uvicorn
from fastapi import FastAPI, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from paddleocr import PaddleOCR
import numpy as np
import cv2
import fitz  # PyMuPDF untuk PDF

app = FastAPI(title="OCR Service - Auto Accuracy")

# Konfigurasi CORS agar Laravel Anda bisa akses
origins = ["http://localhost:8000", "http://127.0.0.1:8000"]
app.add_middleware(CORSMiddleware, allow_origins=origins, allow_methods=["*"], allow_headers=["*"])

# Load model satu kali saat start
print("Memuat model PaddleOCR...")
ocr = PaddleOCR(use_angle_cls=True, lang='id')

def convert_pdf_to_image(file_bytes):
    try:
        doc = fitz.open(stream=file_bytes, filetype="pdf")
        page = doc.load_page(0) # Ambil hal 1
        pix = page.get_pixmap(dpi=200)
        img_array = np.frombuffer(pix.samples, dtype=np.uint8).reshape(pix.h, pix.w, pix.n)
        return cv2.cvtColor(img_array, cv2.COLOR_RGB2BGR)
    except:
        return None

@app.post("/ocr")
async def process_ocr(file: UploadFile = File(...)):
    try:
        contents = await file.read()
        img = None

        if file.filename.lower().endswith('.pdf'):
            img = convert_pdf_to_image(contents)
        else:
            nparr = np.frombuffer(contents, np.uint8)
            img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if img is None:
            return {"status": "error", "message": "File tidak terbaca"}

        result = ocr.ocr(img)

        extracted_text = []
        final_accuracy = "0%"

        # LOGIKA PERBAIKAN INDENTASI DI SINI
        if result and result[0] is not None:
            if 'rec_texts' in result[0] and 'rec_scores' in result[0]:
                extracted_text = result[0]['rec_texts']
                confidences = result[0]['rec_scores']

                if confidences:
                    avg_conf = sum(confidences) / len(confidences)
                    final_accuracy = f"{round(avg_conf * 100, 2)}%"
            else:
                print("Kunci rec_texts/rec_scores tidak ditemukan.")
        else:
            print("Hasil OCR kosong.")

        return {
            "status": "success",
            "result_text": extracted_text,
            "accuracy": final_accuracy
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)

# .\venv\Scripts\activate
# uvicorn main:app --host 0.0.0.0 --port 8001 --reload
