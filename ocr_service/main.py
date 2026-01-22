import uvicorn
from fastapi import FastAPI, UploadFile, File, Form
from fastapi.middleware.cors import CORSMiddleware
from paddleocr import PaddleOCR
import numpy as np
import cv2
import fitz  # PyMuPDF
from jiwer import cer

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
async def process_ocr(
    file: UploadFile = File(...),
    ground_truth: str = Form(None)
):
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

        result = ocr.ocr(img, cls=True)

        extracted_text_list = []
        confidences = []

        # Parse PaddleOCR result
        if result and result[0]:
            for line in result[0]:
                # line structure: [[box], (text, confidence)]
                text = line[1][0]
                score = line[1][1]
                extracted_text_list.append(text)
                confidences.append(score)

        full_text_result = " ".join(extracted_text_list)
        final_accuracy = "0%"

        # --- LOGIKA BARU: CER vs CONFIDENCE ---
        if ground_truth:
            # JIKA ADA KUNCI JAWABAN -> HITUNG PAKAI RUMUS CER
            # Rumus Akurasi = (1 - CER) * 100
            error_rate = cer(ground_truth, full_text_result)
            accuracy_val = max(0, (1 - error_rate) * 100) # Biar gak minus
            final_accuracy = f"{round(accuracy_val, 2)}% (Metode CER)"
            print(f"Mode: Validation (CER). Akurasi: {final_accuracy}")
        else:
            # JIKA TIDAK ADA -> PAKAI RATA-RATA KEYAKINAN AI
            if confidences:
                avg_conf = sum(confidences) / len(confidences)
                final_accuracy = f"{round(avg_conf * 100, 2)}% (Auto Confidence)"
                print(f"Mode: Automatic. Akurasi: {final_accuracy}")

        return {
            "status": "success",
            "filename": file.filename,
            "result_text": extracted_text_list,
            "accuracy": final_accuracy
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)

# .\venv\Scripts\activate
# uvicorn main:app --host 0.0.0.0 --port 8001 --reload
