<div class="container mt-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <?php if(!empty($data['santri']['foto'])) : ?>
                        <img src="<?= BASEURL; ?>/img/santri/<?= $data['santri']['foto']; ?>" alt="Foto Santri" class="rounded-circle mb-3 img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else : ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($data['santri']['nama']); ?>&background=random&size=150" alt="Profile" class="rounded-circle mb-3 img-thumbnail">
                    <?php endif; ?>
                    <h4><?= $data['santri']['nama']; ?></h4>
                    <p class="text-muted mb-1">NIS: <?= $data['santri']['nis']; ?></p>
                    <div class="mt-2">
                        <?php if($data['santri']['status_aktif'] == '1') : ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else : ?>
                            <span class="badge bg-danger">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="d-grid mt-3 gap-2">
                <a href="<?= BASEURL; ?>/santri/edit/<?= $data['santri']['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Edit Data</a>
                <a href="<?= BASEURL; ?>/santri" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-teal"><i class="bi bi-person-lines-fill"></i> Data Pribadi</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-2">
                        <div class="col-sm-4 fw-bold text-muted">Tempat, Tgl Lahir</div>
                        <div class="col-sm-8"><?= htmlspecialchars($data['santri']['tempat_lahir'] ?? '-'); ?>, <?= date('d M Y', strtotime($data['santri']['tanggal_lahir'])); ?> <span class="badge bg-light text-dark border">(Usia: <?= $data['usia']; ?> Tahun)</span></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 fw-bold text-muted">Jenis Kelamin</div>
                        <div class="col-sm-8"><?= ($data['santri']['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 fw-bold text-muted">Alamat Lengkap</div>
                        <div class="col-sm-8"><?= htmlspecialchars($data['santri']['alamat'] ?? '-'); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 fw-bold text-muted">No Kontak Santri</div>
                        <div class="col-sm-8"><?= htmlspecialchars($data['santri']['no_kontak'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 mb-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="card-title fw-bold text-teal"><i class="bi bi-building"></i> Akademik & Asrama</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-sm-5 fw-bold text-muted">Kelas</div>
                                <div class="col-sm-7 fw-semibold"><?= htmlspecialchars($data['santri']['kelas'] ?? '-'); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-5 fw-bold text-muted">Kamar Asrama</div>
                                <div class="col-sm-7 fw-semibold"><?= htmlspecialchars($data['santri']['kamar'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 mb-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0">
                            <h5 class="card-title fw-bold text-teal"><i class="bi bi-people"></i> Orang Tua / Wali</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-sm-5 fw-bold text-muted">Nama Wali</div>
                                <div class="col-sm-7"><?= htmlspecialchars($data['santri']['nama_wali'] ?? '-'); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-5 fw-bold text-muted">Kontak Wali</div>
                                <div class="col-sm-7"><?= htmlspecialchars($data['santri']['kontak_wali'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if(in_array('Perawat', $_SESSION['user_roles']) || in_array('Dokter', $_SESSION['user_roles']) || in_array('Super User', $_SESSION['user_roles'])) : ?>
            <div class="card shadow-sm border-0 border-start border-danger border-4 mt-2">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-danger"><i class="bi bi-clipboard2-pulse"></i> Informasi Kesehatan Medis</h5>
                </div>
                <div class="card-body p-4">
                    <?php if(!empty($data['santri']['riwayat_penyakit'])) : ?>
                        <div class="alert alert-warning border-0 shadow-sm mb-3">
                            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle"></i> Riwayat Penyakit Penting</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($data['santri']['riwayat_penyakit'])); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold text-muted">Riwayat Penyakit</div>
                            <div class="col-sm-8 text-muted fst-italic">Tidak ada riwayat penyakit tercatat</div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($data['santri']['alergi'])) : ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-0">
                            <h6 class="fw-bold mb-1"><i class="bi bi-bug"></i> Alergi</h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($data['santri']['alergi'])); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold text-muted">Alergi</div>
                            <div class="col-sm-8 text-muted fst-italic">Tidak ada riwayat alergi tercatat</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4 text-end">
                <a href="<?= BASEURL; ?>/pemeriksaan/riwayat/<?= $data['santri']['id']; ?>" class="btn btn-teal"><i class="bi bi-clock-history"></i> Lihat Riwayat Pemeriksaan Penuh</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
