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

            <!-- Integrasi Telegram -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-telegram text-primary me-2"></i>Integrasi Telegram</h5>
                    <?php if (!empty($data['telegram_account']) && (int)$data['telegram_account']['is_verified'] === 1) : ?>
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terhubung</span>
                    <?php else : ?>
                        <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Belum Terhubung</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($data['telegram_account']) && (int)$data['telegram_account']['is_verified'] === 1) : ?>
                        <p class="text-muted small mb-2">Akun SantriFit Anda telah terhubung dengan akun Telegram:</p>
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded">
                            <div>
                                <span class="fw-bold text-dark">
                                    <?= !empty($data['telegram_account']['telegram_username']) ? '@' . htmlspecialchars($data['telegram_account']['telegram_username']) : '(Tanpa username publik)'; ?>
                                </span>
                                <div class="text-muted small">Terhubung sejak <?= date('d M Y', strtotime($data['telegram_account']['created_at'])); ?></div>
                            </div>
                            <a href="<?= BASEURL; ?>/profile/telegram" class="btn btn-sm btn-outline-primary">Kelola</a>
                        </div>
                    <?php else : ?>
                        <p class="text-muted small mb-3">Hubungkan akun Anda dengan bot Telegram SantriFit untuk verifikasi akun dan kemudahan akses.</p>
                        <a href="<?= BASEURL; ?>/profile/telegram" class="btn btn-primary btn-sm">
                            <i class="bi bi-telegram me-1"></i> Hubungkan Telegram
                        </a>
                    <?php endif; ?>
                </div>
            </div>

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
