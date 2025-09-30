@extends('layout.main')

@section('content')
    <x-breadcrumb
        :values="[__('menu.transaction.menu'), __('menu.transaction.incoming_letter'), __('menu.general.create')]">
    </x-breadcrumb>

    {{-- KARTU FITUR OCR --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Otomatisasi dengan OCR</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label">Unggah Dokumen & Pindai Otomatis</label>
                    <div>
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

    {{-- FORMULIR PEMBUATAN SURAT LENGKAP --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Formulir Surat Masuk</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('transaction.incoming.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="incoming">
                <div class="row">
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
    {{-- PERUBAHAN 1: Memuat library PDF.js dari file lokal proyek Anda --}}
    <script src="{{ asset('assets/js/pdfjs/pdf.js') }}"></script>

    <script>
        // Membungkus semua skrip dalam window.onload untuk memastikan semua file (termasuk pdf.js) sudah dimuat
        window.onload = function() {
            // Cek sekali lagi apakah library berhasil dimuat
            if (typeof pdfjsLib === 'undefined') {
                console.error('Library pdf.js gagal dimuat. Pastikan file ada di public/assets/js/pdfjs/ dan server berjalan.');
                alert('Error kritis: Library pdf.js gagal dimuat. Periksa konsol (F12) untuk detail.');
                return;
            }

            // PERUBAHAN 2: Konfigurasi worker untuk memuat file dari lokasi lokal
            pdfjsLib.GlobalWorkerOptions.workerSrc = `{{ asset('assets/js/pdfjs/pdf.worker.js') }}`;

            const ocrFile = document.getElementById('ocr_file');
            const loadingSpinner = document.getElementById('ocr-loading');
            const ocrFilenameSpan = document.getElementById('ocr-filename');
            const ocrStatusText = document.getElementById('ocr-status-text');

            ocrFile.addEventListener('change', async function() {
                if (ocrFile.files.length === 0) {
                    ocrFilenameSpan.textContent = 'Belum ada file dipilih';
                    return;
                }

                const file = ocrFile.files[0];
                ocrFilenameSpan.textContent = file.name;
                loadingSpinner.classList.remove('d-none');
                let fileToSend = file;

                if (file.type === 'application/pdf') {
                    ocrStatusText.textContent = 'Mengonversi PDF...';
                    try {
                        const pdfAsDataUri = await readFileAsDataURL(file);
                        const pdf = await pdfjsLib.getDocument(pdfAsDataUri).promise;
                        const page = await pdf.getPage(1);
                        const viewport = page.getViewport({ scale: 2.0 });

                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        await page.render({ canvasContext: context, viewport: viewport }).promise;

                        fileToSend = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                        fileToSend.name = pathinfo(file.name, 'PATHINFO_FILENAME') + '.png';

                    } catch (error) {
                        console.error('PDF Conversion Error:', error);
                        alert('Gagal mengonversi file PDF. File mungkin rusak atau tidak didukung.');
                        loadingSpinner.classList.add('d-none');
                        return;
                    }
                }

                ocrStatusText.textContent = 'Memindai dokumen...';
                const formData = new FormData();
                formData.append('ocr_file', fileToSend, fileToSend.name);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route("ocr.scan") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.error || 'Terjadi masalah pada server.') });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert('Error: ' + data.error);
                        } else {
                            populateForm(data.text);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal melakukan OCR: ' + error.message);
                    })
                    .finally(() => {
                        loadingSpinner.classList.add('d-none');
                        ocrFile.value = ''; // Reset input file
                    });
            });

            function readFileAsDataURL(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }
            function pathinfo(path, options) {
                const basename = path.split('/').pop();
                if (options === 'PATHINFO_FILENAME') {
                    return basename.substr(0, basename.lastIndexOf('.')) || basename;
                }
                return basename;
            }

            function populateForm(text) {
                const lines = text.split('\n');
                let dataExtracted = { nomor: '', tanggal: '', dari: '', perihal: '' };
                lines.forEach((line, index) => {
                    if (line.toLowerCase().includes('nomor') && line.includes(':')) {
                        dataExtracted.nomor = line.split(':')[1]?.trim().split(' ')[0] || '';
                    }
                    const dateRegex = /(\d{1,2}\s+(?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})/i;
                    const dateMatch = line.match(dateRegex);
                    if (dateMatch) { dataExtracted.tanggal = convertDate(dateMatch[0]); }
                    if (line.toLowerCase().includes('kepada yth.') || line.toLowerCase().startsWith('yth.')) {
                        dataExtracted.dari = (lines[index + 1]?.trim() || '') + ' ' + (lines[index + 2]?.trim() || '');
                    }
                    if (line.toLowerCase().includes('hal') && line.includes(':')) {
                        dataExtracted.perihal = line.split(':')[1]?.trim() || '';
                    }
                });
                if (dataExtracted.nomor) document.getElementById('reference_number').value = dataExtracted.nomor;
                if (dataExtracted.tanggal) document.getElementById('letter_date').value = dataExtracted.tanggal;
                if (dataExtracted.dari) document.getElementById('from').value = dataExtracted.dari.trim();
                if (dataExtracted.perihal) document.getElementById('description').value = dataExtracted.perihal;
                alert('Formulir telah diisi berdasarkan hasil OCR. Silakan periksa kembali data sebelum menyimpan.');
            }
            function convertDate(dateString) {
                const months = { 'januari': '01', 'februari': '02', 'maret': '03', 'april': '04', 'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08', 'september': '09', 'oktober': '10', 'november': '11', 'desember': '12' };
                const parts = dateString.toLowerCase().replace(/,/g, '').split(' ');
                if (parts.length === 3) {
                    const day = parts[0].padStart(2, '0');
                    const month = months[parts[1]];
                    const year = parts[2];
                    if (day && month && year) return `${year}-${month}-${day}`;
                }
                return '';
            }
        };
    </script>
@endpush

