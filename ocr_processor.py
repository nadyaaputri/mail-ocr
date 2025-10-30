# ocr_processor.py

import easyocr
import sys
import json # Menggunakan JSON untuk output yang lebih terstruktur
import warnings

# Mengabaikan peringatan spesifik dari PyTorch jika muncul
warnings.filterwarnings("ignore", category=UserWarning, module='torch.amp.autocast_mode')

# Ambil path gambar dari argumen baris perintah
# Kita tambahkan pemeriksaan jumlah argumen untuk keamanan
if len(sys.argv) < 2:
    print(json.dumps({"success": False, "error": "Path gambar tidak diberikan."}))
    sys.exit(1)

image_path = sys.argv[1]

try:
    # Inisialisasi EasyOCR Reader untuk Bahasa Indonesia ('id') dan Inggris ('en')
    # GPU=False agar berjalan di CPU (lebih ringan, tidak perlu GPU khusus)
    reader = easyocr.Reader(['id', 'en'], gpu=False)

    # Baca teks dari gambar
    # paragraph=True menggabungkan teks yang berdekatan menjadi paragraf
    result = reader.readtext(image_path, detail=0, paragraph=True)

    # Gabungkan semua paragraf teks yang terdeteksi menjadi satu string
    full_text = "\n".join(result)

    # Cetak hasil dalam format JSON yang mudah dibaca PHP
    print(json.dumps({"success": True, "text": full_text}))

except Exception as e:
    # Cetak error dalam format JSON jika terjadi masalah
    print(json.dumps({"success": False, "error": str(e)}))
