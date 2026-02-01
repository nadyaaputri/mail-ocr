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
                    <div class="mt-3">
                        <label for="ground_truth_input" class="form-label">Teks Asli Surat (Untuk Uji Akurasi - Opsional)</label>
                        <textarea class="form-control" id="ground_truth_input" rows="3" placeholder="Ketik manual isi surat di sini jika ingin menghitung akurasi..."></textarea>
                    </div> 

                    <div id="accuracy-container" class="mt-2 d-none">
                        <span class="badge bg-info">Akurasi OCR: <span id="accuracy-value">0%</span></span>
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
                        text: 'AI sedang memindai dokumen, mohon tunggu.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // 4. Siapkan Data Form
                    const formData = new FormData();
                    formData.append('file', file);

                    const groundTruth = document.getElementById('ground_truth_input').value;
                    if (groundTruth) {
                        formData.append('ground_truth', groundTruth);
                    }

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

            // --- BAGIAN 3: FUNGSI PARSING LOGIKA (Sama seperti sebelumnya) ---
            function populateForm(lines, accuracy = "0%") {
                console.log("--- Hasil OCR ---", lines);

                // Reset Field
                document.getElementById('reference_number').value = '';
                document.getElementById('letter_date').value = '';
                document.getElementById('description').value = '';
                document.getElementById('from').value = ''; 

                let dataExtracted = { nomor: '', tanggal: '', dari: '', perihal: '' };

                // Regex Helper
                const nomorRegex = /^(nomor|no)(\.|:)?\s*/i;
                const perihalRegex = /^(hal|perihal)(\.|:)?\s*/i;
                const dateRegex = /(\d{1,2})[\s,.-]+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)[\s,.-]+(\d{4})/i;

                // A. PENGIRIM (Kop Surat)
                let potentialSenderLines = [];
                const maxHeaderLines = Math.min(lines.length, 10); 
                
                for (let i = 0; i < maxHeaderLines; i++) {
                    let line = lines[i].trim();
                    let lower = line.toLowerCase();

                    // Stop kalau ketemu keyword isi surat
                    if (lower.startsWith('nomor') || lower.startsWith('no.') || lower.startsWith('lampiran') || lower.startsWith('hal') || lower.startsWith('perihal') || lower.includes('yth.') || lower.includes('kepada')) {
                        break; 
                    }
                    // Skip alamat/kontak
                    if (lower.includes('jalan ') || lower.includes('jl.') || lower.includes('telp') || lower.includes('fax') || lower.includes('email') || lower.includes('website') || line.length < 3) {
                        continue;
                    }
                    potentialSenderLines.push(line);
                }

                if (potentialSenderLines.length > 0) {
                    if (potentialSenderLines.length >= 2 && (potentialSenderLines[0].toUpperCase().includes('PEMERINTAH') || potentialSenderLines[0].toUpperCase().includes('YAYASAN'))) {
                        dataExtracted.dari = potentialSenderLines[0] + ' - ' + potentialSenderLines[1];
                    } else {
                        dataExtracted.dari = potentialSenderLines[0];
                    }
                }

                // B. PARSING ISI
                lines.forEach((line, index) => {
                    let text = line.trim();
                    let lowerLine = text.toLowerCase();

                    // Nomor
                    if (nomorRegex.test(lowerLine) && !dataExtracted.nomor) {
                        let clean = text.replace(nomorRegex, '').replace(/^[:.]/, '').trim();
                        dataExtracted.nomor = clean.length > 3 ? clean.replace(/[^\w\s\/\-.]/g, '') : (lines[index + 1]?.trim() || '').replace(/[^\w\s\/\-.]/g, '');
                    }

                    // Tanggal
                    const dateMatch = line.match(dateRegex);
                    if (dateMatch && !dataExtracted.tanggal) {
                        dataExtracted.tanggal = convertDate(dateMatch[0]);
                    }

                    // Perihal
                    if (perihalRegex.test(lowerLine) && !dataExtracted.perihal) {
                        let cleanHal = text.replace(perihalRegex, '').replace(/^[:.]/, '').trim();
                        dataExtracted.perihal = cleanHal.length > 3 ? cleanHal : [lines[index+1]?.trim(), lines[index+2]?.trim()].filter(Boolean).join(' ');
                    }
                });

                // C. ISI FORM
                if (dataExtracted.nomor) document.getElementById('reference_number').value = dataExtracted.nomor;
                if (dataExtracted.tanggal) document.getElementById('letter_date').value = dataExtracted.tanggal;
                if (dataExtracted.perihal) document.getElementById('description').value = dataExtracted.perihal;
                if (dataExtracted.dari) document.getElementById('from').value = dataExtracted.dari;
            }

            // D. Helper Tanggal
            function convertDate(dateString) {
                if (!dateString) return '';
                const months = { 'januari': '01', 'februari': '02', 'maret': '03', 'april': '04', 'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08', 'september': '09', 'oktober': '10', 'november': '11', 'desember': '12' };
                let cleanString = dateString.replace(/[,.]/g, ' ').trim();
                const parts = cleanString.split(/\s+/); 
                if (parts.length >= 3) {
                    const month = months[parts[1].toLowerCase()];
                    if (month) return `${parts[2]}-${month}-${parts[0].padStart(2, '0')}`;
                }
                return '';
            }
        });
    </script>
@endpush
