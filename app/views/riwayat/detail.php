<?php 
$s = $data['santri']; 
$isPengurusOnly = in_array('Pengurus', $_SESSION['user_roles']) && count(array_intersect(['Super User', 'Dokter', 'Perawat'], $_SESSION['user_roles'])) == 0;
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-clock-history"></i> Timeline Riwayat Kesehatan</h2>
    </div>

    <!-- Header Profil Santri -->
    <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="fw-bold mb-1"><?= $s['nama']; ?></h3>
                    <p class="mb-2 fs-5 opacity-75"><?= $s['nis']; ?></p>
                    <div class="d-flex gap-3">
                        <span><i class="bi bi-door-closed"></i> Kamar: <?= $s['kamar'] ?: '-'; ?></span>
                        <span><i class="bi bi-journal-bookmark"></i> Kelas: <?= $s['kelas'] ?: '-'; ?></span>
                        <span><i class="bi bi-calendar"></i> Lahir: <?= date('d M Y', strtotime($s['tanggal_lahir'])); ?></span>
                    </div>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <div class="bg-white text-dark p-3 rounded h-100">
                        <h6 class="fw-bold text-danger border-bottom pb-2"><i class="bi bi-exclamation-triangle"></i> Kondisi Penting</h6>
                        <p class="mb-1 small"><strong>Alergi:</strong> <?= !empty($s['alergi']) ? $s['alergi'] : '<span class="text-muted">Tidak ada catatan</span>'; ?></p>
                        <p class="mb-0 small"><strong>Riwayat Penyakit:</strong> <?= !empty($s['riwayat_penyakit_penting']) ? $s['riwayat_penyakit_penting'] : '<span class="text-muted">Tidak ada catatan</span>'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Data -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= BASEURL; ?>/riwayat/detail/<?= $s['id']; ?>" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Mulai Tanggal</label>
                    <input type="date" name="mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['mulai']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                    <input type="date" name="akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['akhir']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Cari Keluhan</label>
                    <input type="text" name="keluhan" class="form-control form-control-sm" placeholder="Pusing, demam..." value="<?= htmlspecialchars($data['keluhan']); ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Timeline -->
    <?php if(empty($data['riwayat'])): ?>
        <div class="alert alert-info border-0 shadow-sm text-center p-5">
            <i class="bi bi-info-circle display-4 d-block mb-3"></i>
            <h5>Belum ada riwayat pemeriksaan</h5>
            <p class="mb-0">Tidak ada data rekam medis yang ditemukan untuk santri ini sesuai filter yang diberikan.</p>
        </div>
    <?php else: ?>
        <div class="position-relative border-start border-2 border-primary ms-2 ms-md-4 ps-4 pb-4">
            <?php foreach($data['riwayat'] as $index => $r): ?>
                <?php 
                    $nodeColor = 'bg-primary';
                    if ($r['status'] == 'Selesai') $nodeColor = 'bg-success';
                    else if ($r['status'] == 'Perlu Tindak Lanjut') $nodeColor = 'bg-danger';
                    else if ($r['status'] == 'Menunggu Dokter') $nodeColor = 'bg-warning';
                ?>
                <div class="card shadow-sm border-0 mb-4 position-relative">
                    <div class="position-absolute translate-middle rounded-circle <?= $nodeColor; ?> border border-white border-3 shadow" style="width: 24px; height: 24px; left: -24px; top: 28px;"></div>
                    
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-primary"><?= date('d F Y', strtotime($r['tanggal_pemeriksaan'])); ?></h5>
                            <small class="text-muted"><i class="bi bi-clock"></i> <?= date('H:i', strtotime($r['tanggal_pemeriksaan'])); ?> WIB &bull; <i class="bi bi-person"></i> Petugas: <?= $r['nama_petugas']; ?></small>
                        </div>
                        <span class="badge <?= $nodeColor; ?> fs-6"><?= $r['status']; ?></span>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <h6 class="fw-bold text-muted mb-1">Keluhan Utama</h6>
                                <p class="fs-5 mb-2"><?= htmlspecialchars($r['keluhan_utama']); ?></p>
                                
                                <?php if(!empty($r['gejala'])): ?>
                                    <div class="mb-2">
                                        <?php foreach($r['gejala'] as $g): ?>
                                            <span class="badge bg-danger bg-opacity-75 fw-normal me-1 mb-1"><?= $g['nama_gejala']; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 border-start">
                                <h6 class="fw-bold text-muted mb-1">Tanda Vital</h6>
                                <ul class="list-unstyled small mb-0">
                                    <li>Suhu: <strong><?= $r['suhu_tubuh'] ?: '-'; ?>°C</strong></li>
                                    <li>Tensi: <strong><?= $r['tekanan_darah'] ?: '-'; ?></strong></li>
                                    <li>BB/TB: <strong><?= $r['berat_badan'] ?: '-'; ?>kg / <?= $r['tinggi_badan'] ?: '-'; ?>cm</strong></li>
                                </ul>
                            </div>
                        </div>

                        <?php if(!$isPengurusOnly): ?>
                            <?php if(!empty($r['rekomendasi_id'])): ?>
                                <hr class="opacity-25">
                                <div class="bg-light p-3 rounded">
                                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-clipboard2-pulse"></i> Assessment & Diagnosis Dokter</h6>
                                    <p class="mb-3"><strong>Diagnosis:</strong> <?= nl2br(htmlspecialchars($r['diagnosis_medis'])); ?></p>
                                    
                                    <?php if(!empty($r['resep'])): ?>
                                        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-capsule"></i> Resep Obat</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Obat</th>
                                                        <th>Aturan</th>
                                                        <th>Jml</th>
                                                        <th>Status Pemberian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($r['resep'] as $ro): ?>
                                                    <tr>
                                                        <td><?= $ro['nama_obat']; ?> (<?= $ro['dosis']; ?>)</td>
                                                        <td><?= $ro['aturan_minum']; ?></td>
                                                        <td><?= $ro['jumlah']; ?> <?= $ro['satuan']; ?></td>
                                                        <td>
                                                            <?php if($ro['waktu_diberikan']): ?>
                                                                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Diberikan (<?= date('d/m H:i', strtotime($ro['waktu_diberikan'])); ?>)</span>
                                                            <?php else: ?>
                                                                <span class="text-warning text-dark"><i class="bi bi-clock-history"></i> Belum Diberikan</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
