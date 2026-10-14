<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-teal mb-0"><i class="bi bi-graph-up-arrow me-2"></i> Laporan Kesehatan</h2>
            <p class="text-muted mb-0">Visualisasi data dan statistik kesehatan santri</p>
        </div>
        <button class="btn btn-outline-teal" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak Laporan</button>
    </div>
</div>

<div class="row">
    <!-- Line Chart: Tren Pemeriksaan -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white pb-0 border-bottom-0 pt-3">
                <h5 class="card-title fw-bold">Tren Pemeriksaan (6 Bulan Terakhir)</h5>
            </div>
            <div class="card-body">
                <canvas id="trenChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Pie Chart: Status Pemeriksaan -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white pb-0 border-bottom-0 pt-3">
                <h5 class="card-title fw-bold">Status Pemeriksaan Saat Ini</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Bar Chart: 5 Penyakit/Gejala Terbanyak -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white pb-0 border-bottom-0 pt-3">
                <h5 class="card-title fw-bold">5 Gejala / Keluhan Terbanyak</h5>
            </div>
            <div class="card-body">
                <canvas id="penyakitChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Menyisipkan data PHP ke JavaScript menggunakan JSON -->
<script>
    const dataTren = <?= json_encode($data['tren_bulanan']); ?>;
    const dataPenyakit = <?= json_encode($data['penyakit_populer']); ?>;
    const dataStatus = <?= json_encode($data['status_pemeriksaan']); ?>;
</script>

<!-- Load Chart.js dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Tren Chart (Line)
    const ctxTren = document.getElementById('trenChart').getContext('2d');
    const labelsTren = dataTren.map(item => item.bulan);
    const valuesTren = dataTren.map(item => item.jumlah);
    
    new Chart(ctxTren, {
        type: 'line',
        data: {
            labels: labelsTren.length > 0 ? labelsTren : ['Bulan ini'],
            datasets: [{
                label: 'Jumlah Kunjungan/Pemeriksaan',
                data: valuesTren.length > 0 ? valuesTren : [0],
                borderColor: '#1abc9c',
                backgroundColor: 'rgba(26, 188, 156, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 2. Status Chart (Pie)
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    const labelsStatus = dataStatus.map(item => item.status);
    const valuesStatus = dataStatus.map(item => item.jumlah);
    
    // Warna custom untuk pie chart
    const bgColors = [
        '#3498db', // Menunggu
        '#f1c40f', // Diperiksa
        '#2ecc71', // Selesai
        '#e74c3c'  // Rujuk
    ];

    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: labelsStatus.length > 0 ? labelsStatus : ['Kosong'],
            datasets: [{
                data: valuesStatus.length > 0 ? valuesStatus : [1],
                backgroundColor: bgColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 3. Penyakit Chart (Bar)
    const ctxPenyakit = document.getElementById('penyakitChart').getContext('2d');
    const labelsPenyakit = dataPenyakit.map(item => item.penyakit);
    const valuesPenyakit = dataPenyakit.map(item => item.jumlah);
    
    new Chart(ctxPenyakit, {
        type: 'bar',
        data: {
            labels: labelsPenyakit.length > 0 ? labelsPenyakit : ['Belum ada data'],
            datasets: [{
                label: 'Jumlah Pasien',
                data: valuesPenyakit.length > 0 ? valuesPenyakit : [0],
                backgroundColor: '#3498db',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
