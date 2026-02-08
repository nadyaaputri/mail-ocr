@extends('layout.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pengujian /</span> Uji Akurasi CER</h4>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class='bx bx-layer'></i> File Pengujian</h5>
                </div>
                <div class="card-body mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Upload Variasi Gambar (Pastikan isinya surat yang sama)</label>
                            <input type="file" id="batch_files" class="form-control mb-3" multiple accept="image/*,application/pdf">
                            
                            <div class="d-grid gap-2">
                                <button id="btn_process_all" class="btn btn-dark btn-lg" disabled>
                                    <i class='bx bx-play-circle'></i> JALANKAN PENGUJIAN
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 border-start">
                            <label class="form-label fw-bold text-danger">Ground Truth</label>
                            <textarea id="global_gt" class="form-control mb-2" rows="4"></textarea>
                            
                            <div class="d-flex gap-2">
                                <button id="btn_apply_all" class="btn btn-outline-primary w-50" disabled>
                                    <i class='bx bx-copy-alt'></i> Buat Ground Truth
                                </button>
                                <button id="btn_auto_ref" class="btn btn-warning w-50" disabled>
                                    <i class='bx bx-magic-wand'></i> Scan File Pertama
                                </button>
                            </div>
                            {{-- <small class="text-muted d-block mt-1">*Auto-Scan: File pertama dianggap 'Master' (Gambar Sempurna), hasilnya akan jadi kunci jawaban untuk file lainnya.</small> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">List File</h5>
                    <div>
                        <span class="badge bg-label-primary me-2">Rata-rata CER: <b id="avg_cer">-</b></span>
                        <span class="badge bg-label-success">Rata-rata Akurasi: <b id="avg_acc">-</b></span>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama File</th>
                                <th>Ground Truth</th>
                                <th>Status</th>
                                <th>CER (Error)</th>
                                <th>Real Accuracy</th>
                            </tr>
                        </thead>
                        <tbody id="file_list_body">
                            <tr id="empty_row"><td colspan="6" class="text-center">Belum ada file dipilih.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let filesData = []; 
        const fileInput = document.getElementById('batch_files');
        const tableBody = document.getElementById('file_list_body');
        const processBtn = document.getElementById('btn_process_all');
        const globalGtInput = document.getElementById('global_gt');
        const btnApplyAll = document.getElementById('btn_apply_all');
        const btnAutoRef = document.getElementById('btn_auto_ref');

        // 1. SAAT FILE DIPILIH
        if(fileInput) {
            fileInput.addEventListener('change', function() {
                if(this.files.length === 0) return;

                filesData = Array.from(this.files).map(file => ({
                    file: file,
                    groundTruth: "", 
                    status: "pending",
                    result: null
                }));

                // Aktifkan tombol kontrol
                btnApplyAll.disabled = false;
                btnAutoRef.disabled = false;
                
                renderTable();
                checkReady();
            });
        }

        // 2. LOGIKA OTOMASI: TERAPKAN KE SEMUA
        if(btnApplyAll) {
            btnApplyAll.addEventListener('click', function() {
                const text = globalGtInput.value.trim();
                if(!text) {
                    Swal.fire('Kosong', 'Isi dulu kolom Master Ground Truth di atas', 'warning');
                    return;
                }

                // Loop semua data dan isi GT-nya
                filesData.forEach(item => {
                    item.groundTruth = text;
                });

                renderTable();
                checkReady();
                Swal.fire('Sukses', `Ground Truth diterapkan ke ${filesData.length} file`, 'success');
            });
        }

        // 3. LOGIKA OTOMASI: AUTO-SCAN FILE PERTAMA
        if(btnAutoRef) {
            btnAutoRef.addEventListener('click', async function() {
                if(filesData.length === 0) return;

                // UI Loading
                btnAutoRef.disabled = true;
                btnAutoRef.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> Scanning...`;
                globalGtInput.value = "OCR file pertama...";

                const firstFile = filesData[0];
                const formData = new FormData();
                formData.append('file', firstFile.file);
                // Kita kosongkan GT biar dia cuma balikin text hasil bacaan
                formData.append('ground_truth', ""); 

                try {
                    const res = await fetch('http://localhost:8001/ocr', { method: 'POST', body: formData });
                    const data = await res.json();

                    if(data.status === 'success') {
                        // Masukkan hasil bacaan ke Global Input
                        globalGtInput.value = data.raw_text;
                        
                        // Terapkan ke semua item di array
                        filesData.forEach(item => {
                            item.groundTruth = data.raw_text;
                        });

                        Swal.fire({
                            title: 'Ground Truth Ditetapkan',
                            text: 'Text hasil OCR dari file pertama akan menjadi ground truth untuk semua file.',
                            icon: 'success'
                        });
                    } else {
                        throw new Error(data.message);
                    }
                } catch (e) {
                    Swal.fire('Gagal Auto-Scan', e.message, 'error');
                    globalGtInput.value = "";
                } finally {
                    btnAutoRef.disabled = false;
                    btnAutoRef.innerHTML = `<i class='bx bx-magic-wand'></i> Auto-Scan File #1`;
                    renderTable();
                    checkReady();
                }
            });
        }

        // 4. RENDER TABEL (Disederhanakan)
        function renderTable() {
            tableBody.innerHTML = '';
            if (filesData.length === 0) {
                tableBody.innerHTML = `<tr id="empty_row"><td colspan="6" class="text-center">Belum ada file.</td></tr>`;
                return;
            }

            filesData.forEach((item, index) => {
                // Status GT
                let gtStatus = item.groundTruth 
                    ? `<i class='bx bx-check-circle text-success'></i> Terisi` 
                    : `<span class="text-warning">Belum diisi</span>`;

                // Status Proses
                let statusBadge = `<span class="badge bg-secondary">Pending</span>`;
                if(item.status === 'processing') statusBadge = `<span class="badge bg-info">Processing...</span>`;
                if(item.status === 'done') statusBadge = `<span class="badge bg-success">Selesai</span>`;
                if(item.status === 'error') statusBadge = `<span class="badge bg-danger">Gagal</span>`;

                // Hasil
                let cer = item.result ? `<span class="text-danger fw-bold">${item.result.cer}</span>` : "-";
                let acc = item.result ? `<span class="text-success fw-bold">${item.result.real_accuracy}</span>` : "-";

                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.file.name}</td>
                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${item.groundTruth}">${item.groundTruth || '-'}</td>
                        <td>${statusBadge}</td>
                        <td>${cer}</td>
                        <td>${acc}</td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
        }

        function checkReady() {
            const allFilled = filesData.every(item => item.groundTruth.trim() !== "");
            processBtn.disabled = !allFilled || filesData.length === 0;
            
            // Ubah warna tombol jika siap
            if(!processBtn.disabled) {
                processBtn.classList.remove('btn-dark');
                processBtn.classList.add('btn-primary');
            }
        }

        // 5. PROSES BATCH (JALANKAN TES)
        if(processBtn){
            processBtn.addEventListener('click', async function() {
                processBtn.disabled = true;
                processBtn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> Memproses...`;

                let totalCER = 0;
                let totalAcc = 0;
                let count = 0;

                for (let i = 0; i < filesData.length; i++) {
                    const item = filesData[i];
                    item.status = 'processing';
                    renderTable();

                    const formData = new FormData();
                    formData.append('file', item.file);
                    formData.append('ground_truth', item.groundTruth);

                    try {
                        const res = await fetch('http://localhost:8001/ocr', { method: 'POST', body: formData });
                        const data = await res.json();

                        if (data.status === 'success') {
                            item.result = data;
                            item.status = 'done';
                            
                            totalCER += parseFloat(data.cer);
                            let accVal = parseFloat(data.real_accuracy.replace('%',''));
                            totalAcc += accVal;
                            count++;
                        } else {
                            item.status = 'error';
                        }
                    } catch (err) {
                        console.error(err);
                        item.status = 'error';
                    }
                    renderTable();
                }

                if (count > 0) {
                    document.getElementById('avg_cer').innerText = (totalCER / count).toFixed(4);
                    document.getElementById('avg_acc').innerText = (totalAcc / count).toFixed(2) + "%";
                    Swal.fire('Selesai', `Pengujian Selesai. Akurasi Rata-rata: ${(totalAcc / count).toFixed(2)}%`, 'success');
                }
                
                processBtn.innerHTML = `<i class='bx bx-play-circle'></i> JALANKAN PENGUJIAN`;
            });
        }
    });
</script>
@endpush