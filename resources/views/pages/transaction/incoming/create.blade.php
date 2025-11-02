@extends('layout.main')

@section('content')
    <x-breadcrumb
        :values="[__('menu.transaction.menu'), __('menu.transaction.incoming_letter'), __('menu.general.create')]">
    </x-breadcrumb>

    {{-- KARTU FITUR OCR (Tidak ada perubahan di HTML) --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Otomatisasi dengan OCR</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label">Unggah Dokumen & Pindai Otomatis</label>
                    <div>
                        {{-- ID 'ocr_file' akan digunakan oleh JavaScript --}}
                        <input class="form-control d-none" type="file" id="ocr_file" name="ocr_file" accept="image/*,application/pdf">
                        <label for="ocr_file" class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i> Pilih Dokumen (Gambar/PDF)...
                        </label>
                        <span id="ocr-filename" class="ms-2 text-muted">Belum ada file dipilih</span>
                    </div>
                    <div class="form-text">Pilih file gambar atau PDF. Formulir akan terisi otomatis.</div>
                </div>
                <div class="col-md-4 d-flex align-items-center justify-content-center d-none" id="ocr-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2" id="ocr-status-text">Memindai dokumen...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FORMULIR PEMBUATAN SURAT LENGKAP (Tidak ada perubahan di HTML) --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Formulir Surat Masuk</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('transaction.incoming.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="incoming">
                <div class="row">
                    {{-- Semua ID field di bawah ini (cth: 'reference_number', 'letter_date') digunakan oleh JS --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Nomor Referensi</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                            @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="agenda_number" class="form-label">Nomor Agenda</label>
                            <input type="text" class="form-control @error('agenda_number') is-invalid @enderror" id="agenda_number" name="agenda_number" value="{{ old('agenda_number') }}">
                            @error('agenda_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="from" class="form-label">Pengirim</label>
                            <input type="text" class="form-control @error('from') is-invalid @enderror" id="from" name="from" value="{{ old('from') }}">
                            @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="letter_date" class="form-label">Tanggal Surat</label>
                            <input type="date" class="form-control @error('letter_date') is-invalid @enderror" id="letter_date" name="letter_date" value="{{ old('letter_date') }}">
                            @error('letter_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_date" class="form-label">Tanggal Diterima</label>
                            <input type="date" class="form-control @error('received_date') is-invalid @enderror" id="received_date" name="received_date" value="{{ old('received_date') }}">
                            @error('received_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="classification_code" class="form-label">Kode Klasifikasi</label>
                            <select class="form-select @error('classification_code') is-invalid @enderror" id="classification_code" name="classification_code">
                                <option selected disabled>Pilih klasifikasi...</option>
                                @foreach($classifications as $classification)
                                    <option value="{{ $classification->code }}" {{ old('classification_code') == $classification->code ? 'selected' : '' }}>{{ $classification->code }} - {{ $classification->type }}</option>
                                @endforeach
                            </select>
                            @error('classification_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Lampiran (Opsional)</label>
                            <input class="form-control @error('attachments') is-invalid @enderror" type="file" id="attachments" name="attachments[]" multiple>
                            @error('attachments')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi/Perihal</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="note" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="2">{{ old('note') }}</textarea>
                    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">{{ __('menu.general.save') }}</button>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        // --- BLOK SCRIPT INI TELAH DIMODIFIKASI ---
        document.addEventListener('DOMContentLoaded', function() {
            const ocrFile = document.getElementById('ocr_file');
            const loadingSpinner = document.getElementById('ocr-loading');
            const ocrFilenameSpan = document.getElementById('ocr-filename');
            const ocrStatusText = document.getElementById('ocr-status-text');

            // URL API Python (FastAPI). Pastikan ini benar dan server Python berjalan.
            const OCR_API_URL = 'http://localhost:8001/ocr'; // <-- UBAHAN PENTING

            ocrFile.addEventListener('change', async function() {
                if (ocrFile.files.length === 0) {
                    ocrFilenameSpan.textContent = 'Belum ada file dipilih';
                    return;
                }

                const file = ocrFile.files[0];
                ocrFilenameSpan.textContent = file.name;
                loadingSpinner.classList.remove('d-none');
                ocrStatusText.textContent = 'Mengirim ke API OCR...';

                const formData = new FormData();
                // 'file' harus cocok dengan nama parameter di 'main.py' (FastAPI)
                formData.append('file', file); // <-- UBAHAN NAMA FIELD

                // Hapus _token dan header Laravel, karena ini memanggil API eksternal

                fetch(OCR_API_URL, { // <-- UBAHAN URL
                    method: 'POST',
                    body: formData,
                    // Tidak perlu headers 'X-CSRF-TOKEN' atau 'X-Requested-With'
                })
                    .then(response => {
                        if (!response.ok) {
                            // Tangani error HTTP (misal: API Python mati atau error 500)
                            return response.json().then(err => {
                                throw new Error(err.message || 'Server API OCR tidak merespon/error.')
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Sesuaikan dengan format respons JSON dari FastAPI
                        if (data.status === 'success' && data.result_text) {
                            ocrStatusText.textContent = 'Memproses teks...';
                            // Kirim array 'result_text' langsung ke populateForm
                            populateForm(data.result_text); // <-- UBAHAN PARAMETER
                        } else {
                            // Tangani error logis dari API (misal: "status": "error")
                            throw new Error(data.message || 'Gagal memproses OCR.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        ocrStatusText.textContent = 'Gagal: ' + error.message;
                        // Tampilkan pesan error di UI
                        alert('Gagal melakukan OCR: ' + error.message + '\n(Cek konsol untuk detail & pastikan API Python berjalan)');
                    })
                    .finally(() => {
                        // Sembunyikan loading dan reset input file
                        loadingSpinner.classList.add('d-none');
                        ocrFile.value = '';
                        ocrFilenameSpan.textContent = 'Belum ada file dipilih';
                    });
            });

                // --- GANTI FUNGSI LAMA ANDA DENGAN VERSI BARU INI ---
                function populateForm(lines) {
                    // #1: LOG UNTUK MELIHAT HASIL MENTAH DARI API PYTHON
                    console.log("--- Teks Mentah dari OCR (Array) ---");
                    console.log(lines);
                    console.log("---------------------------------");

                    let dataExtracted = { nomor: '', tanggal: '', dari: '', perihal: '' };

                    lines.forEach((line, index) => {
                        line = line.trim();
                        if (!line) return;

                        // Ekstraksi Nomor Surat
                        if (!dataExtracted.nomor && (line.toLowerCase().includes('nomor'))) {
                            let parts = line.split(':');
                            if (parts.length > 1) {
                                let extractedNomor = parts.slice(1).join(':').trim();
                                dataExtracted.nomor = extractedNomor.replace(/[^\w\s\/\-.]/g, '');
                            }
                        }

                        // Ekstraksi Tanggal Surat
                        const dateRegex = /(\d{1,2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})/i;
                        const dateMatch = line.match(dateRegex);
                        if (!dataExtracted.tanggal && dateMatch && dateMatch[0]) {
                            dataExtracted.tanggal = convertDate(dateMatch[0]);
                        }

                        // Ekstraksi Pengirim
                        if (!dataExtracted.dari && (line.toLowerCase().includes('kepada yth'))) {
                            dataExtracted.dari = lines[index + 1]?.trim() || '';
                        }

                        // Ekstraksi Perihal
                        if (!dataExtracted.perihal && (line.toLowerCase().startsWith('hal') || line.toLowerCase().startsWith('perihal'))) {
                            let parts = line.split(':');
                            if (parts.length > 1) {
                                dataExtracted.perihal = parts.slice(1).join(':').trim();
                            }
                        }
                    });

                    // #2: LOG UNTUK MELIHAT APA YANG BERHASIL DIEKSTRAK
                    console.log("--- Data yang Berhasil Diekstrak ---");
                    console.log(dataExtracted);
                    console.log("---------------------------------");

                    // Mengisi formulir
                    if (dataExtracted.nomor) document.getElementById('reference_number').value = dataExtracted.nomor;
                    if (dataExtracted.tanggal) document.getElementById('letter_date').value = dataExtracted.tanggal;
                    if (dataExtracted.dari) document.getElementById('from').value = dataExtracted.dari;
                    if (dataExtracted.perihal) document.getElementById('description').value = dataExtracted.perihal;

                    alert('Formulir telah diisi berdasarkan hasil OCR. Silakan periksa kembali data sebelum menyimpan.');
                }

                    // Ekstraksi Perihal: Mencari kata "Hal" atau "Perihal" diikuti titik dua
                    if (!dataExtracted.perihal && (line.toLowerCase().startsWith('hal') || line.toLowerCase().startsWith('perihal'))) {
                        let parts = line.split(':');
                        if (parts.length > 1) {
                            dataExtracted.perihal = parts.slice(1).join(':').trim();
                        }
                    }
                });

                // #2: Menampilkan hasil ekstraksi di konsol
                console.log("--- Data yang Berhasil Diekstrak ---", dataExtracted);

                // Mengisi formulir
                if (dataExtracted.nomor) document.getElementById('reference_number').value = dataExtracted.nomor;
                if (dataExtracted.tanggal) document.getElementById('letter_date').value = dataExtracted.tanggal;
                if (dataExtracted.dari) document.getElementById('from').value = dataExtracted.dari;
                if (dataExtracted.perihal) document.getElementById('description').value = dataExtracted.perihal;

                alert('Formulir telah diisi berdasarkan hasil OCR. Silakan periksa kembali data sebelum menyimpan.');
            }

            // Fungsi helper konversi tanggal (Tidak ada perubahan)
            function convertDate(dateString) {
                const months = { 'januari': '01', 'februari': '02', 'maret': '03', 'april': '04', 'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08', 'september': '09', 'oktober': '10', 'november': '11', 'desember': '12' };
                const parts = dateString.toLowerCase().replace(/,/g, '').split(' ').filter(Boolean); // filter(Boolean) u/ hapus spasi ganda
                if (parts.length === 3) {
                    const day = parts[0].padStart(2, '0');
                    const month = months[parts[1]];
                    const year = parts[2];
                    if (day && month && year) return `${year}-${month}-${day}`;
                }
                return '';
            }
        });
    </script>
@endpush
