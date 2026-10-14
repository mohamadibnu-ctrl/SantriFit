<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-robot text-primary"></i> Asisten Kesehatan AI</h2>
    </div>

    <div class="row">
        <!-- Panel Kiri: Input Form -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100 border-top border-primary border-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-3">Konsultasi Awal</h5>
                    <p class="text-muted small">Ceritakan keluhan atau gejala yang dialami santri secara detail (contoh: "Demam 38.5°C sejak 2 hari, batuk, pilek dan lemas").</p>
                    
                    <form id="aiForm">
                        <div class="mb-3">
                            <textarea class="form-control" id="promptInput" rows="6" placeholder="Ketik keluhan pasien di sini..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2" id="btnAnalyze">
                            <i class="bi bi-magic"></i> Analisis Keluhan
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    <div class="alert alert-info border-0 small">
                        <i class="bi bi-info-circle-fill"></i> <strong>Cara kerja:</strong> Sistem AI akan mengekstrak informasi gejala dan memberikan edukasi umum serta peringatan dini.
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Hasil AI -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100 bg-light border-top border-success border-4">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h5 class="card-title fw-bold mb-0 text-success">Hasil Analisis & Saran</h5>
                </div>
                <div class="card-body p-4 position-relative">
                    
                    <!-- Loading Overlay -->
                    <div id="loadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-90 d-none flex-column justify-content-center align-items-center" style="z-index: 10; border-radius: 0.375rem;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 fw-bold text-primary">AI sedang menganalisis gejala...</p>
                    </div>

                    <!-- Output Area -->
                    <div id="resultArea" class="h-100 d-flex flex-column justify-content-center align-items-center text-muted text-center p-3">
                        <i class="bi bi-chat-square-dots display-1 mb-3 opacity-25"></i>
                        <p>Ketik keluhan di panel sebelah kiri untuk mendapatkan analisis awal dari Asisten AI.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('aiForm');
    const input = document.getElementById('promptInput');
    const btn = document.getElementById('btnAnalyze');
    const overlay = document.getElementById('loadingOverlay');
    const resultArea = document.getElementById('resultArea');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const prompt = input.value.trim();
        if(!prompt) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';
        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');

        try {
            const response = await fetch('<?= BASEURL; ?>/aiassistant/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ prompt: prompt })
            });
            
            const data = await response.json();
            
            if(data.status === 'success') {
                resultArea.classList.remove('justify-content-center', 'align-items-center', 'text-center');
                resultArea.innerHTML = marked.parse(data.result);
                
                const blockquotes = resultArea.querySelectorAll('blockquote');
                blockquotes.forEach(bq => {
                    bq.classList.add('alert', 'alert-warning', 'border-0', 'small', 'mt-4');
                });
            } else {
                resultArea.innerHTML = `<div class="alert alert-danger">Error processing request.</div>`;
            }
        } catch (error) {
            resultArea.innerHTML = `<div class="alert alert-danger">Connection error.</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-magic"></i> Analisis Keluhan';
            overlay.classList.remove('d-flex');
            overlay.classList.add('d-none');
        }
    });
});
</script>
