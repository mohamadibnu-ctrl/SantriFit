<?php $s = $data['santri']; ?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-clock-history"></i> Rekam Medis: <?= htmlspecialchars($s['nama']); ?></h2>
        <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-3">
                    <h5 class="fw-bold text-primary"><?= htmlspecialchars($s['nis']); ?></h5>
                    <p class="mb-0 text-muted">Kamar: <?= htmlspecialchars($s['kamar'] ?? '-'); ?></p>
                    <p class="mb-0 text-muted">Kelas: <?= htmlspecialchars($s['kelas'] ?? '-'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if(empty($data['riwayat'])) : ?>
                <div class="alert alert-info border-0 shadow-sm bg-light text-muted">
                    <i class="bi bi-info-circle me-2"></i> Belum ada catatan rekam medis (pemeriksaan) untuk santri ini.
                </div>
            <?php else : ?>
                <div class="position-relative border-start border-2 border-teal ms-3 ps-4 pb-4">
                    <?php foreach($data['riwayat'] as $r) : ?>
                        <div class="card shadow-sm border-0 mb-4 position-relative">
                            <span class="position-absolute translate-middle bg-teal rounded-circle" style="width: 16px; height: 16px; left: -24px; top: 24px;"></span>
                            
                            <div class="card-header bg-white pt-3 pb-2 border-bottom-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-0 text-teal"><i class="bi bi-calendar-event"></i> <?= date('d F Y', strtotime($r['tanggal_pemeriksaan'])); ?></h6>
                                    <small class="text-muted"><i class="bi bi-clock"></i> <?= date('H:i', strtotime($r['tanggal_pemeriksaan'])); ?> - Ditangani oleh: <?= htmlspecialchars($r['nama_petugas']); ?></small>
                                </div>
                                <?php
                                    $badge = 'bg-secondary';
                                    if ($r['status'] == 'Menunggu Dokter') $badge = 'bg-warning text-dark';
                                    if ($r['status'] == 'Sedang Ditangani') $badge = 'bg-primary';
                                    if ($r['status'] == 'Perlu Tindak Lanjut') $badge = 'bg-danger';
                                    if ($r['status'] == 'Selesai') $badge = 'bg-success';
                                ?>
                                <span class="badge <?= $badge; ?>"><?= $r['status']; ?></span>
                            </div>
                            <div class="card-body pt-0 pb-3">
                                <p class="mb-2"><strong>Keluhan:</strong> <?= htmlspecialchars($r['keluhan_utama']); ?></p>
                                <?php if(!empty($r['hasil_pemeriksaan'])) : ?>
                                    <p class="mb-2 text-muted"><strong>Hasil:</strong> <?= htmlspecialchars($r['hasil_pemeriksaan']); ?></p>
                                <?php endif; ?>
                                <a href="<?= BASEURL; ?>/pemeriksaan/detail/<?= $r['id']; ?>" class="btn btn-sm btn-outline-teal mt-2"><i class="bi bi-journal-text"></i> Lihat Detail Lengkap</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
