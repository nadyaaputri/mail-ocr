@extends('layout.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pengujian /</span> Uji Akurasi (Batch Test)</h4>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3>🚀 Uji Performa OCR</h3>
                    <p class="text-muted">Pilih banyak gambar surat sekaligus (misal: 10-15 file) untuk menghitung rata-rata akurasi sistem.</p>

                    <input type="file" id="batch_files" class="form-control mb-3" multiple accept="image/*,application/pdf">

                    <button id="btn_start_test" class="btn btn-primary btn-lg" disabled>
                        <i class="bx bx-play"></i> Mulai Pengujian
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Hasil Pengujian</h5>
                    <div class="alert alert-info mb-0">
                        Rata-rata Akurasi: <strong id="grand_average" style="font-size: 1.2rem">0%</strong>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama File</th>
                                <th>Status</th>
                                <th>Skor Keyakinan (AI)</th>
                            </tr>
                        </thead>
                        <tbody id="result_body">
                            <tr id="empty_row">
                                <td colspan="4" class="text-center">Belum ada data pengujian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const fileInput = document.getElementById('batch_files');
        const startBtn = document.getElementById('btn_start_test');
        const resultBody = document.getElementById('result_body');
        const grandAverageLabel = document.getElementById('grand_average');

        let totalAccuracy = 0;
        let successCount = 0;

        // 1. Cek jika file dipilih, hidupkan tombol start
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                startBtn.disabled = false;
                startBtn.innerHTML = `<i class="bx bx-play"></i> Mulai Pengujian (${this.files.length} File)`;
            }
        });

        // 2. Logika Utama Pengujian
        startBtn.addEventListener('click', async function() {
            const files = Array.from(fileInput.files);

            // Reset tabel
            resultBody.innerHTML = '';
            totalAccuracy = 0;
            successCount = 0;
            grandAverageLabel.textContent = "Menghitung...";
            startBtn.disabled = true;

            let index = 1;

            // LOOPING FILE (Satu per satu dikirim ke Python)
            for (const file of files) {
                // Buat baris sementara (Loading...)
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index}</td>
                    <td>${file.name}</td>
                    <td><span class="badge bg-warning">Memproses...</span></td>
                    <td>-</td>
                `;
                resultBody.appendChild(row);

                // Kirim ke API Python
                const formData = new FormData();
                formData.append('file', file);

                try {
                    // Pastikan URL ini sesuai dengan URL API Python kamu
                    const response = await fetch('http://localhost:8001/ocr', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();

                    if (data.status === 'success') {
                        // Konversi "98.5%" (string) jadi 98.5 (angka)
                        let accString = data.accuracy || "0%";
                        let accNumber = parseFloat(accString.replace('%', ''));

                        // Update Baris Tabel
                        row.innerHTML = `
                            <td>${index}</td>
                            <td>${file.name}</td>
                            <td><span class="badge bg-success">Sukses</span></td>
                            <td class="fw-bold text-primary">${accString}</td>
                        `;

                        // Tambahkan ke kalkulasi rata-rata
                        totalAccuracy += accNumber;
                        successCount++;
                    } else {
                        throw new Error('Gagal scan');
                    }
                } catch (error) {
                    row.innerHTML = `
                        <td>${index}</td>
                        <td>${file.name}</td>
                        <td><span class="badge bg-danger">Gagal</span></td>
                        <td class="text-danger">Error</td>
                    `;
                }

                // Scroll ke bawah otomatis biar terlihat prosesnya
                row.scrollIntoView({ behavior: 'smooth' });

                // Update Rata-rata Sementara (Realtime)
                if (successCount > 0) {
                    const currentAvg = (totalAccuracy / successCount).toFixed(2);
                    grandAverageLabel.textContent = currentAvg + "%";
                }

                index++;
            }

            startBtn.disabled = false;
            startBtn.innerHTML = `<i class="bx bx-refresh"></i> Uji Ulang`;

            Swal.fire('Selesai!', `Pengujian selesai. Rata-rata akurasi: ${(totalAccuracy / successCount).toFixed(2)}%`, 'success');
        });
    </script>
@endsection
