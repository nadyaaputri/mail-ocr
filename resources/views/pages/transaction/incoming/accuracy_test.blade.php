@extends('layout.main')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pengujian /</span> Batch Accuracy Test</h4>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Pilih Sampel Surat (Gambar/PDF)</label>
                                <input type="file" id="batch_files" class="form-control" multiple accept="image/*,application/pdf">
                                <div class="form-text">Kamu bisa memilih 10-20 file sekaligus.</div>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="use_cer_mode">
                                <label class="form-check-label" for="use_cer_mode">
                                    Aktifkan Mode Validasi Ilmiah (Butuh Input Manual)
                                </label>
                            </div>
                            <div class="col-md-4 text-end">
                                <button id="btn_start_test" class="btn btn-primary w-100" disabled>
                                    <i class="bx bx-play-circle me-1"></i> Mulai Hitung
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">Hasil Pengujian (Confidence Metric)</h5>
                        <div class="badge bg-white text-dark p-2">
                            Rata-rata Akurasi: <span id="grand_average_display" class="fw-bold">0%</span>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Sampel Surat</th>
                                <th class="text-center">Total Karakter (N)</th>
                                <th class="text-center">Total Kata</th>
                                <th class="text-center">Min. Keyakinan</th>
                                <th class="text-center fw-bold">Akurasi AI (%)</th>
                            </tr>
                            </thead>
                            <tbody id="result_body">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Silakan upload file untuk memulai pengujian.</td>
                            </tr>
                            </tbody>
                            <tfoot class="table-dark" id="table_footer" style="display: none;">
                            <tr>
                                <td colspan="2" class="fw-bold text-end">RATA-RATA TOTAL</td>
                                <td class="text-center fw-bold" id="avg_chars">-</td>
                                <td class="text-center fw-bold" id="avg_words">-</td>
                                <td class="text-center">-</td>
                                <td class="text-center fw-bold text-warning" id="final_avg_acc">0%</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const fileInput = document.getElementById('batch_files');
        const startBtn = document.getElementById('btn_start_test');
        const resultBody = document.getElementById('result_body');
        const tableFooter = document.getElementById('table_footer');
        const grandDisplay = document.getElementById('grand_average_display');

        // Variabel Penampung Data Rata-rata
        let totalFiles = 0;
        let sumAccuracy = 0;
        let sumChars = 0;
        let sumWords = 0;

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                startBtn.disabled = false;
                startBtn.innerHTML = `<i class="bx bx-play-circle"></i> Mulai Hitung (${this.files.length} Sampel)`;
            }
        });

        startBtn.addEventListener('click', async function() {
            const files = Array.from(fileInput.files);

            // Reset Tampilan
            resultBody.innerHTML = '';
            totalFiles = 0;
            sumAccuracy = 0;
            sumChars = 0;
            sumWords = 0;
            tableFooter.style.display = 'none';
            grandDisplay.textContent = "Menghitung...";
            startBtn.disabled = true;

            let index = 1;

            for (const file of files) {
                // 1. Buat Baris Loading
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>${index}</td>
                <td>${file.name}</td>
                <td class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
            `;
                resultBody.appendChild(row);

                // 2. Kirim ke Python
                const formData = new FormData();
                formData.append('file', file);

                if (useCerMode.checked) {
                    // Pause loop, minta input manual untuk file ini
                    const manualText = prompt(`Masukkan teks asli untuk file: ${file.name} (untuk hitung CER)`);
                    if (manualText) {
                        formData.append('ground_truth', manualText);
                    }
                }

                try {
                    // Pastikan Port 8001 Sesuai settingan Python kamu
                    const response = await fetch('http://localhost:8001/ocr', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.status === 'success') {
                        // --- LOGIKA PERHITUNGAN DATA ---
                        // Mengambil data mentah dari teks hasil scan
                        const fullText = data.result_text.join(" ");
                        const charCount = fullText.length; // Total Karakter (N)
                        const wordCount = fullText.split(/\s+/).length; // Total Kata

                        // Bersihkan persen dari string akurasi (misal "98.5%" jadi 98.5)
                        let accVal = parseFloat(data.accuracy.replace('%', ''));

                        // Update Baris Tabel dengan Data Asli
                        row.innerHTML = `
                        <td>${index}</td>
                        <td>${file.name}</td>
                        <td class="text-center">${charCount}</td>
                        <td class="text-center">${wordCount}</td>
                        <td class="text-center text-muted"><small>Auto</small></td>
                        <td class="text-center fw-bold text-success">${data.accuracy}</td>
                    `;

                        // Tambahkan ke statistik total
                        sumAccuracy += accVal;
                        sumChars += charCount;
                        sumWords += wordCount;
                        totalFiles++;

                    } else {
                        throw new Error('Gagal');
                    }
                } catch (error) {
                    row.innerHTML = `
                    <td>${index}</td>
                    <td>${file.name} <span class="badge bg-danger">Error</span></td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="text-center text-danger">0%</td>
                `;
                }

                // Update Rata-Rata Realtime
                if (totalFiles > 0) {
                    const currentAvg = (sumAccuracy / totalFiles).toFixed(2);
                    grandDisplay.textContent = currentAvg + "%";
                }

                row.scrollIntoView({ behavior: 'smooth' });
                index++;
            }

            // 3. Tampilkan Footer Rata-rata Akhir
            if (totalFiles > 0) {
                document.getElementById('avg_chars').textContent = (sumChars / totalFiles).toFixed(1);
                document.getElementById('avg_words').textContent = (sumWords / totalFiles).toFixed(1);
                document.getElementById('final_avg_acc').textContent = (sumAccuracy / totalFiles).toFixed(2) + "%";
                tableFooter.style.display = 'table-footer-group';

                Swal.fire('Selesai!', 'Perhitungan batch selesai.', 'success');
            }

            startBtn.disabled = false;
            startBtn.innerHTML = `<i class="bx bx-refresh"></i> Uji Ulang`;
        });
    </script>
@endsection
