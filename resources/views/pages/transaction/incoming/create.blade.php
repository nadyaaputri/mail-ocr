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
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        {{-- Input File Asli (Disembunyikan) --}}
                        <input class="form-control d-none" type="file" id="ocr_file" name="ocr_file" accept="image/*,application/pdf">

                        {{-- Tombol Custom --}}
                        <label for="ocr_file" class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i> Pilih Dokumen (Gambar/PDF)...
                        </label>

                        {{-- Nama File --}}
                        <span id="ocr-filename" class="text-muted">Belum ada file dipilih</span>

                        {{-- BADGE TIMER (Awalnya disembunyikan / d-none) --}}
                        <div id="timer-badge" class="badge bg-label-warning d-none">
                            <i class="bx bx-time-five me-1"></i> <span id="timer-display">0.00s</span>
                        </div>
                    </div>
{{--                    <div class="mt-3">--}}
{{--                        <label for="ground_truth_input" class="form-label">Teks Asli Surat (Untuk Uji Akurasi - Opsional)</label>--}}
{{--                        <textarea class="form-control" id="ground_truth_input" rows="3" placeholder="Ketik manual isi surat di sini jika ingin menghitung akurasi..."></textarea>--}}
{{--                    </div>--}}

                    <div id="accuracy-container" class="mt-2 d-none">
                        <span class="badge bg-info">Confidence Score OCR: <span id="accuracy-value">0%</span></span>
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

    {{-- FORMULIR PEMBUATAN SURAT LENGKAP) --}}
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
                            <label for="reference_number" class="form-label">Nomor Surat</label>
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
                            <input
                                type="date"
                                class="form-control @error('received_date') is-invalid @enderror"
                                id="received_date"
                                name="received_date"
                                value="{{ old('received_date', date('Y-m-d')) }}"
>
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
    {{-- Pastikan library SweetAlert dimuat --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- BAGIAN 1: DEFINISI ELEMEN ---
            const ocrFile = document.getElementById('ocr_file');
            const ocrFilenameSpan = document.getElementById('ocr-filename');
            const loadingSpinner = document.getElementById('ocr-loading'); // Elemen spinner lama (opsional)

            // Elemen Timer Baru
            const timerBadge = document.getElementById('timer-badge');
            const timerDisplay = document.getElementById('timer-display');

            // Elemen Akurasi
            const accContainer = document.getElementById('accuracy-container');
            const accValue = document.getElementById('accuracy-value');

            const OCR_API_URL = 'http://localhost:8001/ocr';

            // Variabel Global untuk Timer
            let timerInterval;
            let startTime;

            // --- BAGIAN 2: EVENT LISTENER ---
            if (ocrFile) {
                ocrFile.addEventListener('change', async function() {
                    // Cek jika user membatalkan pilih file
                    if (ocrFile.files.length === 0) {
                        ocrFilenameSpan.textContent = 'Belum ada file dipilih';
                        return;
                    }

                    const file = ocrFile.files[0];

                    // 1. Update UI Nama File
                    ocrFilenameSpan.textContent = file.name;
                    ocrFilenameSpan.classList.remove('text-muted');
                    ocrFilenameSpan.classList.add('fw-bold', 'text-dark');

                    // 2. MULAI TIMER (Inilah yang kurang di kode lama!)
                    if (timerBadge && timerDisplay) {
                        clearInterval(timerInterval); // Reset timer lama jika ada

                        // Tampilkan badge & Reset warna ke Kuning (Proses)
                        timerBadge.classList.remove('d-none', 'bg-label-success', 'bg-label-danger');
                        timerBadge.classList.add('bg-label-warning');
                        timerDisplay.textContent = "0.00s";

                        // Catat waktu mulai
                        startTime = Date.now();

                        // Jalankan mesin waktu (update tiap 50 milidetik)
                        timerInterval = setInterval(() => {
                            const elapsedTime = (Date.now() - startTime) / 1000;
                            timerDisplay.textContent = elapsedTime.toFixed(2) + "s";
                        }, 50);
                    }

                    // 3. Tampilkan Loading (SweetAlert & Spinner)
                    if(loadingSpinner) loadingSpinner.classList.remove('d-none');

                    Swal.fire({
                        title: 'Sedang Membaca...',
                        text: 'Proses OCR Sedang Berlangsung.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // 4. Siapkan Data Form
                    const formData = new FormData();
                    formData.append('file', file);

                    // const groundTruth = document.getElementById('ground_truth_input').value;
                    // if (groundTruth) {
                    //     formData.append('ground_truth', groundTruth);
                    // }

                    try {
                        // 5. Kirim ke API OCR
                        const response = await fetch(OCR_API_URL, {
                            method: 'POST',
                            body: formData,
                        });

                        // --- STOP TIMER (Saat respon diterima) ---
                        clearInterval(timerInterval);
                        const finalTime = timerDisplay ? timerDisplay.textContent : '0s';

                        if (!response.ok) {
                            throw new Error(`Gagal terhubung ke OCR (Status: ${response.status})`);
                        }

                        const data = await response.json();

                        if (data.status === 'success') {
                            // SUKSES: Ubah Timer jadi Hijau
                            if (timerBadge) {
                                timerBadge.classList.remove('bg-label-warning');
                                timerBadge.classList.add('bg-label-success');
                            }

                            // Update UI Akurasi
                            if (accValue && accContainer) {
                                accValue.textContent = data.accuracy || "0%";
                                accContainer.classList.remove('d-none');
                            }

                            // Normalisasi Data (Array vs String)
                            let lines = [];
                            if (Array.isArray(data.result_text)) {
                                lines = data.result_text;
                            } else if (typeof data.result_text === 'string') {
                                lines = data.result_text.split('\n');
                            }

                            // Jalankan Logika Parsing
                            populateForm(lines, data.accuracy);

                            // Notifikasi Sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Selesai!',
                                html: `Waktu: <b>${finalTime}</b><br>Akurasi: <b>${data.accuracy || 'N/A'}</b>`,
                                timer: 4000
                            });

                        } else {
                            throw new Error(data.message || 'Gagal memproses gambar');
                        }

                    } catch (error) {
                        // ERROR: Stop timer & Ubah jadi Merah
                        clearInterval(timerInterval);
                        if (timerBadge) {
                            timerBadge.classList.remove('bg-label-warning');
                            timerBadge.classList.add('bg-label-danger');
                        }

                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Scan',
                            text: error.message
                        });

                    } finally {
                        // Sembunyikan spinner lama & reset input file agar bisa pilih ulang
                        if(loadingSpinner) loadingSpinner.classList.add('d-none');
                        ocrFile.value = '';
                    }
                });
            }

            // --- BAGIAN 3: FUNGSI PARSING LOGIKA (OPTIMIZED) ---
            function populateForm(lines, accuracy = "0%") {
                console.log("--- Raw OCR Lines ---", lines);

                // 1. Bersihkan Array (Hapus baris kosong/terlalu pendek)
                const cleanLines = lines
                    .map(l => l.trim())
                    .filter(l => l.length > 2); // Abaikan noise 1-2 huruf

                // 2. Siapkan Wadah Data
                let data = { nomor: '', tanggal: '', perihal: '', dari: '' };

                // 3. REGEX PATTERNS (LEBIH KUAT)

                // Pola Tanggal Indo (dd Bulan yyyy)
                const dateRegex = /(\d{1,2})\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember|Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)\s+(\d{4})/i;

                // Pola Label Nomor (Nomor, No, Ref, Our Ref)
                const labelNomorRegex = /^(?:Nomor|Nomer|No\.|Ref|Our Ref)(?:[\s:.-]*)(.*)/i;

                // Pola Format Nomor Surat (Angka/Huruf/Romawi/Tahun) -> Contoh: 420/112/SMK/2023
                const formatNomorRegex = /([0-9]{2,5}[\/.\-][A-Za-z0-9.\-\/]+)/;

                // Pola Perihal
                const labelPerihalRegex = /^(?:Perihal|Hal|Tentang|Subject)(?:[\s:.-]*)(.*)/i;

                // --- LOGIKA EKSTRAKSI ---

                // A. CARI TANGGAL (Prioritas Utama)
                // Cari di seluruh baris, karena tanggal bisa di pojok kanan atas atau bawah
                for (let line of cleanLines) {
                    const dateMatch = line.match(dateRegex);
                    if (dateMatch) {
                        // Simpan tanggal, lalu STOP (biasanya tanggal surat cuma satu yang utama)
                        // Kecuali kita mau validasi tanggal mana yang paling masuk akal
                        data.tanggal = convertDate(dateMatch[0]);
                        break;
                    }
                }

                // B. CARI NOMOR SURAT
                // Strategi 1: Cari baris yang diawali "Nomor:"
                for (let i = 0; i < cleanLines.length; i++) {
                    const line = cleanLines[i];
                    const matchLabel = line.match(labelNomorRegex);

                    if (matchLabel) {
                        let isi = matchLabel[1].trim();
                        // Jika isinya kosong (misal: "Nomor :"), ambil baris bawahnya
                        if (isi.length < 3 && cleanLines[i+1]) {
                            isi = cleanLines[i+1];
                        }
                        data.nomor = cleanString(isi);
                        break; // Ketemu label, stop
                    }
                }

                // Strategi 2 (Fallback): Jika Strategi 1 gagal, cari pola "123/ABC/2023" di 10 baris pertama
                if (!data.nomor) {
                    for (let i = 0; i < Math.min(cleanLines.length, 15); i++) {
                        // Skip jika baris ini tanggal (biar gak ketukar 12 Januari)
                        if (dateRegex.test(cleanLines[i])) continue;

                        const matchFormat = cleanLines[i].match(formatNomorRegex);
                        if (matchFormat) {
                            data.nomor = cleanString(matchFormat[1]);
                            break;
                        }
                    }
                }

                // C. CARI PERIHAL / HAL
                for (let i = 0; i < cleanLines.length; i++) {
                    const line = cleanLines[i];
                    const matchHal = line.match(labelPerihalRegex);

                    if (matchHal) {
                        let isi = matchHal[1].trim();

                        // Cek Multiline: Jika perihal pendek atau kosong, mungkin nyambung ke bawah
                        // Logic: Ambil baris ini + baris depannya (selama bukan "Kepada" atau "Lampiran")
                        if (isi.length < 30 && cleanLines[i+1]) {
                            const nextLine = cleanLines[i+1];
                            const keywordStop = /^(Kepada|Yth|Lampiran|Di|Tempat)/i;

                            if (!keywordStop.test(nextLine)) {
                                isi += " " + nextLine;
                            }
                        }
                        data.perihal = cleanString(isi);
                        break;
                    }
                }

                // D. CARI PENGIRIM (DARI KOP SURAT) - PALING TRICKY
                // Logika: Ambil baris paling atas yang HURUF BESAR (Kapital),
                // BUKAN "PEMERINTAH", "DINAS" (Header umum), dan BUKAN "Nomor/Lampiran".

                let candidateSender = "";
                // Cek 8 baris pertama saja
                for (let i = 0; i < Math.min(cleanLines.length, 8); i++) {
                    const line = cleanLines[i];
                    const upper = line.toUpperCase();

                    // 1. Skip Kata Kunci Header Umum (Kita cari nama instansi spesifiknya)
                    if (upper.includes("PEMERINTAH") || upper.includes("KABUPATEN") || upper.includes("KOTA") || upper.includes("PROVINSI") || upper.includes("REPUBLIK")) {
                        continue;
                    }

                    // 2. Skip Metadata Surat (JANGAN SAMPAI "NOMOR:..." JADI PENGIRIM)
                    if (labelNomorRegex.test(line) || dateRegex.test(line) || labelPerihalRegex.test(line) || upper.includes("LAMPIRAN")) {
                        continue;
                    }

                    // 3. Skip Alamat/Kontak
                    if (upper.includes("JALAN") || upper.includes("JL.") || upper.includes("TELP") || upper.includes("FAX") || upper.includes("EMAIL") || upper.includes("WEBSITE") || upper.includes("HTTP")) {
                        continue;
                    }

                    // 4. Syarat Calon Pengirim:
                    // - Panjang > 5 karakter
                    // - Huruf Besar Semua (Biasanya nama instansi di KOP itu kapital)
                    // - Tidak mengandung angka dominan
                    if (line.length > 5 && isAllUpperCase(line) && !/\d{3,}/.test(line)) {
                        candidateSender = line;

                        // Cek baris bawahnya, siapa tau nama instansinya 2 baris
                        // Contoh: "DINAS PENDIDIKAN" (baris 1) "SEKOLAH MENENGAH ATAS 1" (baris 2)
                        if (cleanLines[i+1] && isAllUpperCase(cleanLines[i+1]) && !cleanLines[i+1].includes("JALAN")) {
                            candidateSender += " " + cleanLines[i+1];
                        }
                        break; // Ketemu kandidat kuat, stop loop
                    }
                }

                // Fallback: Jika tidak ketemu yang kapital, ambil baris pertama yang "bersih"
                if (!candidateSender && cleanLines.length > 0) {
                    // Cari baris pertama yang bukan metadata
                    const fallback = cleanLines.find(l =>
                        !l.match(labelNomorRegex) &&
                        !l.match(dateRegex) &&
                        !l.toUpperCase().includes("PEMERINTAH")
                    );
                    if(fallback) candidateSender = fallback;
                }

                data.dari = cleanString(candidateSender);

                // --- ISI FORM ---
                console.log("--- Extracted Data ---", data); // Debugging

                if(data.nomor) document.getElementById('reference_number').value = data.nomor;
                if(data.tanggal) document.getElementById('letter_date').value = data.tanggal;
                if(data.perihal) document.getElementById('description').value = data.perihal;
                if(data.dari) document.getElementById('from').value = data.dari;
            }

            // --- HELPER FUNCTIONS ---

            // Cek apakah string huruf besar semua (mengabaikan simbol)
            function isAllUpperCase(str) {
                const clean = str.replace(/[^a-zA-Z]/g, '');
                return clean.length > 0 && clean === clean.toUpperCase();
            }

            // Membersihkan string dari karakter aneh OCR (: . | _ ~) di awal/akhir
            function cleanString(str) {
                if(!str) return "";
                // Hapus : atau . di awal string, dan spasi berlebih
                return str.replace(/^[:.\s]+/, '').replace(/[:.\s]+$/, '').trim();
            }

            // Konversi Tanggal Indo -> HTML Date
            function convertDate(dateString) {
                if (!dateString) return '';

                // Map Bulan (Support singkatan umum)
                const months = {
                    'januari': '01', 'jan': '01',
                    'februari': '02', 'feb': '02', 'peb': '02',
                    'maret': '03', 'mar': '03',
                    'april': '04', 'apr': '04',
                    'mei': '05', 'may': '05',
                    'juni': '06', 'jun': '06',
                    'juli': '07', 'jul': '07',
                    'agustus': '08', 'agu': '08', 'aug': '08',
                    'september': '09', 'sep': '09',
                    'oktober': '10', 'okt': '10',
                    'november': '11', 'nov': '11', 'nop': '11',
                    'desember': '12', 'des': '12'
                };

                // Bersihkan string (misal: "Medan, 12 Januari 2023" -> "12 Januari 2023")
                // Ambil hanya bagian yang cocok dengan pola tanggal
                const regex = /(\d{1,2})\s+([a-zA-Z]{3,})\s+(\d{4})/;
                const match = dateString.match(regex);

                if (match) {
                    const day = match[1].padStart(2, '0');
                    const monthStr = match[2].toLowerCase();
                    const year = match[3];
                    const month = months[monthStr];

                    if (month) return `${year}-${month}-${day}`;
                }
                return '';
            }
        });
    </script>
@endpush
