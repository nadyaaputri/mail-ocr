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
