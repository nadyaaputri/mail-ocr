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
        document.addEventListener('DOMContentLoaded', function() {
            // --- BAGIAN 1: DEFINISI ELEMEN ---
            const ocrFile = document.getElementById('ocr_file');
            const loadingSpinner = document.getElementById('ocr-loading');
            const ocrFilenameSpan = document.getElementById('ocr-filename');
            const ocrStatusText = document.getElementById('ocr-status-text');

            const OCR_API_URL = 'http://localhost:8001/ocr'; // Pastikan API Python berjalan di port 8001

            // --- BAGIAN 2: EVENT LISTENER ---
            ocrFile.addEventListener('change', async function() {
                if (ocrFile.files.length === 0) {
                    ocrFilenameSpan.textContent = 'Belum ada file dipilih';
                    return;
                }

                console.log("Mulai proses: file dipilih...");

                const file = ocrFile.files[0];
                ocrFilenameSpan.textContent = file.name;
                loadingSpinner.classList.remove('d-none');
                ocrStatusText.textContent = 'Mengirim ke API OCR...';

                const formData = new FormData();
                formData.append('file', file);

                // AMBIL TEKS DARI TEXTAREA GROUND TRUTH
                const groundTruth = document.getElementById('ground_truth_input').value;
                if (groundTruth) {
                    formData.append('ground_truth', groundTruth);
                }

                fetch(OCR_API_URL, {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Ambil akurasi otomatis dari hasil kiriman Python
                            const autoAccuracy = data.accuracy || "0%";

                            // Tampilkan di UI
                            const accValue = document.getElementById('accuracy-value');
                            const accContainer = document.getElementById('accuracy-container');

                            if (accValue && accContainer) {
                                accValue.textContent = autoAccuracy;
                                accContainer.classList.remove('d-none');
                            }

                            // Panggil fungsi isi form dengan membawa nilai akurasi
                            populateForm(data.result_text, autoAccuracy);
                        }
                    })

                    .catch(error => {
                        console.error('Error:', error);
                        ocrStatusText.textContent = 'Gagal: ' + error.message;
                        alert('Gagal melakukan OCR: ' + error.message + '\n(Cek konsol untuk detail & pastikan API Python berjalan)');
                    })
                    .finally(() => {
                        loadingSpinner.classList.add('d-none');
                        ocrFile.value = '';
                        ocrFilenameSpan.textContent = 'Belum ada file dipilih';
                    });
            }); // <-- Akhir dari addEventListener

            // --- BAGIAN 3: FUNGSI PARSING OCR (VERSI BARU) ---
            function populateForm(lines) {
                console.log("--- Teks Mentah dari OCR ---");
                console.log(lines);

                let dataExtracted = { nomor: '', tanggal: '', dari: '', perihal: '' };

                // Iterasi setiap baris
                lines.forEach((line, index) => {
                    let lowerLine = line.toLowerCase().trim();

                    // 1. Ekstraksi Nomor
                    if (lowerLine === 'nomor' && !dataExtracted.nomor) {
                        let nextLine = lines[index + 1]?.trim() || '';
                        if (nextLine.startsWith(':')) {
                            // Ambil teks setelah ':', bersihkan, dan perbaiki bug regex
                            dataExtracted.nomor = nextLine.substring(1).trim().replace(/[^\w\s\/\-.]/g, '');
                        }
                    }

                    // 2. Ekstraksi Tanggal
                    const dateRegex = /(\d{1,2}\s+(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})/i;
                    const dateMatch = line.match(dateRegex);
                    if (dateMatch && dateMatch[0] && !dataExtracted.tanggal) {
                        dataExtracted.tanggal = convertDate(dateMatch[0]);
                    }

                    // 3. Ekstraksi Perihal (Hal)
                    if (lowerLine === 'hal' && !dataExtracted.perihal) {
                        let perihalLines = [];
                        perihalLines.push(lines[index + 1]?.trim() || '');
                        perihalLines.push(lines[index + 2]?.trim() || '');
                        perihalLines.push(lines[index + 3]?.trim() || '');
                        dataExtracted.perihal = perihalLines.filter(Boolean).join(' ');
                    }

                    // 4. Ekstraksi Pengirim (From)
                    if (line.includes('') && !dataExtracted.dari) {
                        dataExtracted.dari = line.trim();
                    }
                });

                console.log("--- Data yang Berhasil Diekstrak ---");
                console.log(dataExtracted);

                // Mengisi formulir
                if (dataExtracted.nomor) document.getElementById('reference_number').value = dataExtracted.nomor;
                if (dataExtracted.tanggal) document.getElementById('letter_date').value = dataExtracted.tanggal;
                if (dataExtracted.dari) document.getElementById('from').value = dataExtracted.dari;
                if (dataExtracted.perihal) document.getElementById('description').value = dataExtracted.perihal;

                // Kita mengambil teks dari elemen span yang kita buat sebelumnya
                const accuracyElement = document.getElementById('accuracy-value');
                const accuracy = accuracyElement ? accuracyElement.textContent : 'N/A';

                Swal.fire({
                    title: 'OCR Berhasil!',
                    html: `Formulir telah diisi.<br><b>Skor Akurasi: ${accuracy}</b>`,
                    text: 'Formulir telah diisi. Silakan periksa kembali data sebelum menyimpan.',
                    icon: 'success',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#696cff' // Ini adalah warna primer tema Sneat Anda
                });
            }

            // --- BAGIAN 4: FUNGSI HELPER TANGGAL ---
            function convertDate(dateString) {
                const months = { 'januari': '01', 'februari': '02', 'maret': '03', 'april': '04', 'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08', 'september': '09', 'oktober': '10', 'november': '11', 'desember': '12' };
                const parts = dateString.toLowerCase().replace(/,/g, '').split(' ').filter(Boolean);
                if (parts.length === 3) {
                    const day = parts[0].padStart(2, '0');
                    const month = months[parts[1]];
                    const year = parts[2];
                    if (day && month && year) return `${year}-${month}-${day}`;
                }
                return '';
            }

        }); // <-- Akhir dari DOMContentLoaded
    </script>
@endpush
