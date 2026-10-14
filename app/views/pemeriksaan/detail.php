<?php
$p = $data['pemeriksaan'];
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-file-earmark-medical"></i> Detail Pemeriksaan</h2>
        <div>
            <?php if(in_array('Dokter', $_SESSION['user_roles']) || in_array('Super User', $_SESSION['user_roles']) || (in_array('Perawat', $_SESSION['user_roles']) && $p['status'] == 'Menunggu Dokter')) : ?>
                <a href="<?= BASEURL; ?>/pemeriksaan/edit/<?= $p['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Update Data</a>
            <?php endif; ?>
            <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-teal text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Info Pasien</h5>
                </div>
                <div class="card-body p-4 text-center">
                    <h4 class="fw-bold text-dark"><?= htmlspecialchars($p['nama_santri']); ?></h4>
                    <p class="text-muted mb-3">NIS: <?= htmlspecialchars($p['nis']); ?></p>
                    <a href="<?= BASEURL; ?>/pemeriksaan/riwayat/<?= $p['santri_id']; ?>" class="btn btn-sm btn-outline-teal w-100"><i class="bi bi-journal-medical"></i> Lihat Rekam Medis Santri</a>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Tgl Diperiksa
                        <span class="fw-bold"><?= date('d M Y, H:i', strtotime($p['tanggal_pemeriksaan'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Petugas (Triase)
                        <span class="fw-bold"><?= htmlspecialchars($p['nama_petugas']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Status Saat Ini
                        <?php
                            $badge = 'bg-secondary';
                            if ($p['status'] == 'Menunggu Dokter') $badge = 'bg-warning text-dark';
                            if ($p['status'] == 'Sedang Ditangani') $badge = 'bg-primary';
                            if ($p['status'] == 'Perlu Tindak Lanjut') $badge = 'bg-danger';
                            if ($p['status'] == 'Selesai') $badge = 'bg-success';
                        ?>
                        <span class="badge <?= $badge; ?> fs-6"><?= $p['status']; ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white pb-0 border-0 pt-4">
                    <h5 class="fw-bold text-teal"><i class="bi bi-activity"></i> Tanda-Tanda Vital</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Suhu</div>
                                <div class="fs-4"><?= $p['suhu_tubuh'] ? $p['suhu_tubuh'] . ' °C' : '-'; ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Tensi</div>
                                <div class="fs-4"><?= $p['tekanan_darah'] ?: '-'; ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Berat</div>
                                <div class="fs-4"><?= $p['berat_badan'] ? $p['berat_badan'] . ' kg' : '-'; ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Tinggi</div>
                                <div class="fs-4"><?= $p['tinggi_badan'] ? $p['tinggi_badan'] . ' cm' : '-'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-teal mb-3 border-bottom pb-2"><i class="bi bi-chat-left-dots"></i> Keluhan Utama</h5>
                    <p class="fs-5"><?= nl2br(htmlspecialchars($p['keluhan_utama'])); ?></p>
                    
                    <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-tags"></i> Gejala Terkait:</h6>
                    <div>
                        <?php if(empty($data['gejala'])) : ?>
                            <span class="text-muted fst-italic">Tidak ada gejala spesifik yang dicentang.</span>
                        <?php else : ?>
                            <?php foreach($data['gejala'] as $g) : ?>
                                <span class="badge bg-danger bg-opacity-75 p-2 me-1 mb-1 fs-6"><?= $g['nama_gejala']; ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 bg-light border-start border-teal border-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-teal"><i class="bi bi-clipboard2-check"></i> Penanganan Medis & Catatan</h5>
                    
                    <h6 class="fw-bold text-muted mb-1">Hasil Pemeriksaan Fisik / Diagnosa:</h6>
                    <div class="bg-white p-3 rounded border mb-3">
                        <?= !empty($p['hasil_pemeriksaan']) ? nl2br(htmlspecialchars($p['hasil_pemeriksaan'])) : '<em class="text-muted">Belum ada hasil pemeriksaan diisi.</em>'; ?>
                    </div>
                    
                    <h6 class="fw-bold text-muted mb-1">Catatan Petugas/Dokter:</h6>
                    <div class="bg-white p-3 rounded border">
                        <?= !empty($p['catatan_petugas']) ? nl2br(htmlspecialchars($p['catatan_petugas'])) : '<em class="text-muted">Belum ada catatan tambahan.</em>'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
