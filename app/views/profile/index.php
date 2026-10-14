<div class="container mt-4">
    <div class="row">
        <!-- Kolom Kiri: Profil Basic -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <img src="https://ui-avatars.com/api/?name=<?= $data['user']['username']; ?>&background=0D8ABC&color=fff&size=128" alt="Profile" class="rounded-circle mb-3 img-thumbnail">
                    <h4><?= $data['user']['username']; ?></h4>
                    
                    <div class="mt-3">
                        <?php foreach($data['user']['roles'] as $role) : ?>
                            <span class="badge bg-primary"><?= $role; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kolom Kanan: Detail & Aksi Khusus -->
        <div class="col-md-8">
            <?php if(in_array('Santri', $data['user']['roles'])) : ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white border-0 pt-3 pb-2">
                    <h5 class="card-title fw-bold"><i class="bi bi-person-badge"></i> Data Kesehatan Saya</h5>
                </div>
                <div class="card-body p-4">
                    <?php if(!empty($data['detail_santri'])) : ?>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold text-muted">NIS</div>
                            <div class="col-sm-9"><?= $data['detail_santri']['nis']; ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold text-muted">Nama Lengkap</div>
                            <div class="col-sm-9"><?= $data['detail_santri']['nama']; ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold text-muted">Kamar / Kelas</div>
                            <div class="col-sm-9"><?= $data['detail_santri']['kamar']; ?> / <?= $data['detail_santri']['kelas']; ?></div>
                        </div>
                        
                        <div class="alert alert-info mt-3 border-0">
                            <strong>Info Privasi:</strong> Data rekam medis dan resep obat Anda hanya bisa diakses oleh Anda, Dokter, dan Perawat.
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?= BASEURL; ?>/riwayat/detail/<?= $data['detail_santri']['id']; ?>" class="btn btn-success w-100 py-2"><i class="bi bi-clock-history"></i> Buka Timeline Rekam Medis</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning border-0"><i class="bi bi-exclamation-triangle"></i> Data profil santri Anda belum terhubung dengan sistem. Silakan hubungi pengurus.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="card-title fw-bold">Pengaturan Akun</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Fitur ubah kata sandi dan pengaturan akun akan tersedia pada versi berikutnya.</p>
                </div>
            </div>
        </div>
    </div>
</div>
