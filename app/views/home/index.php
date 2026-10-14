<?php 
$roles = $_SESSION['user_roles'] ?? []; 
$s = $data['stats'];
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-speedometer2"></i> Dashboard Utama</h2>
        <span class="text-muted">Selamat datang kembali, <strong><?= $_SESSION['username']; ?></strong></span>
    </div>

    <?php if(empty($roles)): ?>
        <div class="alert alert-warning">Anda tidak memiliki hak akses (role) di sistem ini. Silakan hubungi Super User.</div>
    <?php endif; ?>

    <!-- BLOK SANTRI -->
    <?php if(isset($s['santri']) && !empty($s['santri'])): ?>
        <?php $ps = $s['santri']; ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-teal text-white border-0 py-3"><h5 class="mb-0"><i class="bi bi-person-badge"></i> Panel Santri</h5></div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center border-end">
                        <div class="p-3">
                            <h1 class="display-3 text-teal fw-bold mb-0"><?= $ps['total_pemeriksaan']; ?></h1>
                            <span class="text-muted small text-uppercase fw-semibold">Total Kunjungan Medis</span>
                        </div>
                    </div>
                    <div class="col-md-8 ps-md-4">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history"></i> Pemeriksaan Terakhir</h6>
                        <?php if(isset($ps['pemeriksaan_terakhir']) && !empty($ps['pemeriksaan_terakhir'])): ?>
                            <?php $akhir = $ps['pemeriksaan_terakhir']; ?>
                            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3 border">
                                <div>
                                    <p class="mb-1 fw-semibold text-dark"><?= htmlspecialchars($akhir['keluhan_utama']); ?></p>
                                    <small class="text-muted"><?= date('d M Y', strtotime($akhir['tanggal_pemeriksaan'])); ?></small>
                                </div>
                                <span class="badge bg-secondary"><?= $akhir['status']; ?></span>
                            </div>
                        <?php else: ?>
                            <p class="text-muted fst-italic bg-light p-3 rounded border">Belum ada catatan medis.</p>
                        <?php endif; ?>
                        <div class="mt-2">
                            <a href="<?= BASEURL; ?>/profile" class="btn btn-outline-teal"><i class="bi bi-person-lines-fill"></i> Lihat Detail Profil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- BLOK PERAWAT -->
    <?php if(isset($s['perawat'])): ?>
        <?php $p = $s['perawat']; ?>
        <h5 class="fw-bold text-teal mb-3"><i class="bi bi-person-heart"></i> Panel Perawat</h5>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-teal text-white h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $p['pemeriksaan_hari_ini']; ?></h1>
                        <span class="small fw-semibold text-uppercase opacity-75 mt-2">Pemeriksaan Hari Ini</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $p['menunggu_dokter']; ?></h1>
                        <span class="small fw-semibold text-uppercase opacity-75 mt-2">Menunggu Dokter</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column justify-content-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $p['tindak_lanjut']; ?></h1>
                        <span class="small fw-semibold text-uppercase opacity-75 mt-2">Perlu Tindak Lanjut</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-light border-0 shadow-sm h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-center text-muted"><i class="bi bi-lightning-charge"></i> Aksi Cepat</h6>
                        <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-teal mb-2"><i class="bi bi-plus-circle"></i> Pemeriksaan Baru</a>
                        <a href="<?= BASEURL; ?>/pemberian" class="btn btn-outline-secondary"><i class="bi bi-capsule"></i> Instruksi Obat</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- BLOK DOKTER -->
    <?php if(isset($s['dokter'])): ?>
        <?php $d = $s['dokter']; ?>
        <h5 class="fw-bold text-info mb-3"><i class="bi bi-heart-pulse"></i> Panel Dokter</h5>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white h-100 shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $d['perlu_direview']; ?></h1>
                        <span class="small fw-semibold text-uppercase opacity-75 mt-2">Antrean Review</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100 shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $d['pemeriksaan_hari_ini']; ?></h1>
                        <span class="small fw-semibold text-uppercase opacity-75 mt-2">Pasien Aktif Hari Ini</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-secondary text-white h-100 shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $d['rekomendasi_belum_selesai']; ?></h1>
                        <span class="small fw-semibold text-uppercase opacity-75 mt-2">Draft Rekomendasi</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100 p-2 bg-light d-flex flex-column justify-content-center">
                     <h6 class="fw-bold border-bottom pb-2 mb-3 text-center text-muted"><i class="bi bi-ui-checks"></i> Cek Antrean</h6>
                     <a href="<?= BASEURL; ?>/rekomendasi" class="btn btn-info text-white btn-sm mt-2 w-100 py-2"><i class="bi bi-journal-medical"></i> Buka Modul Rekomendasi</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- BLOK PENGURUS -->
    <?php if(isset($s['pengurus'])): ?>
        <?php $pg = $s['pengurus']; ?>
        <div class="card shadow-sm border-0 mb-4 border-start border-warning border-4">
            <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart"></i> Statistik Pengurus</h5></div>
            <div class="card-body row text-center py-4">
                <div class="col-4 border-end">
                    <h2 class="text-teal fw-bold mb-1"><?= $pg['total_santri']; ?></h2>
                    <span class="text-muted small fw-semibold text-uppercase">Total Santri</span>
                </div>
                <div class="col-4 border-end">
                    <h2 class="text-warning fw-bold mb-1"><?= $pg['pemeriksaan_hari_ini']; ?></h2>
                    <span class="text-muted small fw-semibold text-uppercase">Kunjungan Klinik Hari Ini</span>
                </div>
                <div class="col-4">
                    <h2 class="text-danger fw-bold mb-1"><?= $pg['santri_sakit']; ?></h2>
                    <span class="text-muted small fw-semibold text-uppercase">Santri Sedang Sakit</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- BLOK SUPER USER -->
    <?php if(isset($s['super_user'])): ?>
        <?php $su = $s['super_user']; ?>
        <div class="card shadow-sm border-0 mb-4 border-start border-dark border-4">
            <div class="card-header bg-dark text-white py-3"><h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock"></i> Sistem Administratif (Super User)</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 text-center align-middle table-hover">
                        <thead class="bg-light small text-uppercase text-muted">
                            <tr>
                                <th class="py-3">Total Santri</th>
                                <th class="py-3">Santri Aktif</th>
                                <th class="py-3">Pengguna (User)</th>
                                <th class="py-3">Tenaga Medis</th>
                                <th class="py-3">Total Riwayat Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fs-3 fw-bold py-4"><?= $su['total_santri']; ?></td>
                                <td class="fs-3 fw-bold text-success py-4"><?= $su['santri_aktif']; ?></td>
                                <td class="fs-3 fw-bold py-4"><?= $su['total_user']; ?></td>
                                <td class="fs-3 fw-bold py-4"><?= $su['total_dokter'] + $su['total_perawat']; ?> <br><span class="small fw-semibold text-muted fs-6">(D:<?= $su['total_dokter']; ?> P:<?= $su['total_perawat']; ?>)</span></td>
                                <td class="fs-3 fw-bold text-teal py-4"><?= $su['total_pemeriksaan']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
