@extends('layout.main')

@section('content')
    <x-breadcrumb
        :values="[__('menu.transaction.menu'), __('menu.transaction.incoming_letter'), __('menu.general.create')]">
    </x-breadcrumb>

    {{-- KARTU BARU UNTUK FITUR OCR --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Otomatisasi dengan OCR</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label for="ocr_file" class="form-label">Unggah Dokumen (Gambar)</label>
                    <div class="input-group">
                        <input class="form-control" type="file" id="ocr_file" name="ocr_file" accept="image/*">
                        <button class="btn btn-primary" type="button" id="scan-ocr-btn">
                            <i class="bx bx-scan me-1"></i> Scan Dokumen
                        </button>
                    </div>
                    <div class="form-text">Unggah file gambar (JPG, PNG) dari surat yang sudah dipindai untuk mengisi formulir secara otomatis.</div>
                </div>
                {{-- Area untuk menampilkan status loading --}}
                <div class="col-md-4 d-flex align-items-center justify-content-center d-none" id="ocr-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Memindai dokumen...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- FORMULIR PEMBUATAN SURAT --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Formulir Surat Masuk</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('transaction.incoming.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="incoming">

                {{-- Kolom-kolom formulir --}}
                <div class="mb-3">
                    <label for="reference_number" class="form-label">Nomor Referensi</label>
                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                    @error('reference_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="from" class="form-label">Pengirim</label>
                    <input type="text" class="form-control @error('from') is-invalid @enderror" id="from" name="from" value="{{ old('from') }}">
                    @error('from')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="letter_date" class="form-label">Tanggal Surat</label>
                    <input type="date" class="form-control @error('letter_date') is-invalid @enderror" id="letter_date" name="letter_date" value="{{ old('letter_date') }}">
                    @error('letter_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Kolom lainnya tetap sama --}}

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi/Perihal</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol Submit --}}
                <button type="submit" class="btn btn-primary">{{ __('menu.general.save') }}</button>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ocrFile = document.getElementById('ocr_file');
            const scanBtn = document.getElementById('scan-ocr-btn');
            const loadingSpinner = document.getElementById('ocr-loading');

            scanBtn.addEventListener('click', function() {
                if (ocrFile.files.length === 0) {
                    alert('Silakan pilih file gambar terlebih dahulu.');
                    return;
                }

                // Tampilkan loading
                loadingSpinner.classList.remove('d-none');
                scanBtn.disabled = true;

                const formData = new FormData();
                formData.append('ocr_file', ocrFile.files[0]);
                formData.append('_token', '{{ csrf_token() }}'); // Tambahkan CSRF token

                // Kirim request AJAX ke OcrController
                fetch('{{ route("ocr.scan") }}', {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Terjadi masalah pada server.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert('Error: ' + data.error);
                        } else {
                            // Panggil fungsi untuk mengisi form dengan teks hasil OCR
                            populateForm(data.text);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal melakukan OCR. Silakan cek konsol untuk detail.');
                    })
                    .finally(() => {
                        // Sembunyikan loading
                        loadingSpinner.classList.add('d-none');
                        scanBtn.disabled = false;
                    });
            });

            function populateForm(text) {
                // --- LOGIKA SEDERHANA UNTUK EKSTRAKSI DATA ---
                // Anda bisa membuat logika ini jauh lebih canggih dengan Regex
                const lines = text.split('\n');
                let dataExtracted = {
                    nomor: '',
                    tanggal: '',
                    dari: ''
                };

                lines.forEach(line => {
                    // Ekstraksi Nomor Surat (mencari kata "Nomor:")
                    if (line.toLowerCase().includes('nomor:')) {
                        dataExtracted.nomor = line.split(':')[1]?.trim() || '';
                    }

                    // Ekstraksi Tanggal Surat (mencari format tanggal)
                    // Contoh Regex sederhana untuk format DD MMMM YYYY
                    const dateRegex = /(\d{1,2}\s+(?:Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+\d{4})/i;
                    const dateMatch = line.match(dateRegex);
                    if (dateMatch) {
                        // Convert "19 September 2025" ke format "YYYY-MM-DD"
                        dataExtracted.tanggal = convertDate(dateMatch[0]);
                    }

                    // Ekstraksi Pengirim (mencari kata "Yth.")
                    if (line.toLowerCase().includes('yth.')) {
                        // Ambil baris setelah "Yth." sebagai pengirim
                        const nextLineIndex = lines.indexOf(line) + 1;
                        dataExtracted.dari = lines[nextLineIndex]?.trim() || '';
                    }
                });

                // Isi nilai ke dalam field form
                if (dataExtracted.nomor) document.getElementById('reference_number').value = dataExtracted.nomor;
                if (dataExtracted.tanggal) document.getElementById('letter_date').value = dataExtracted.tanggal;
                if (dataExtracted.dari) document.getElementById('from').value = dataExtracted.dari;

                alert('Formulir telah diisi berdasarkan hasil OCR. Silakan periksa kembali data sebelum menyimpan.');
            }

            function convertDate(dateString) {
                const months = { 'januari': '01', 'februari': '02', 'maret': '03', 'april': '04', 'mei': '05', 'juni': '06', 'juli': '07', 'agustus': '08', 'september': '09', 'oktober': '10', 'november': '11', 'desember': '12' };
                const parts = dateString.toLowerCase().split(' ');
                if (parts.length === 3) {
                    const day = parts[0].padStart(2, '0');
                    const month = months[parts[1]];
                    const year = parts[2];
                    if (day && month && year) {
                        return `${year}-${month}-${day}`;
                    }
                }
                return '';
            }
        });
    </script>
@endpush
