<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Flash Message -->
            <?php Flasher::flash(); ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom pt-4 pb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-telegram text-primary me-2"></i>Hubungkan Akun Telegram</h4>
                        <p class="text-muted small mb-0">Integrasikan akun SantriFit Anda dengan Telegram untuk notifikasi dan layanan terintegrasi.</p>
                    </div>
                    <a href="<?= BASEURL; ?>/profile" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                    </a>
                </div>

                <div class="card-body p-4">
                    <?php 
                    $isLinked = !empty($data['telegram_account']) && (int)$data['telegram_account']['is_verified'] === 1;
                    ?>

                    <?php if ($isLinked) : ?>
                        <!-- ================= AKUN TERHUBUNG ================= -->
                        <div class="alert alert-success d-flex align-items-center border-0 p-3 mb-4" role="alert">
                            <i class="bi bi-check-circle-fill fs-2 me-3 text-success"></i>
                            <div>
                                <h5 class="alert-heading fw-bold mb-1">Status: Telegram Terhubung</h5>
                                <p class="mb-0 small">Akun SantriFit Anda telah terhubung secara resmi dan terverifikasi dengan Telegram.</p>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-secondary text-uppercase small">Detail Akun Terhubung</h6>
                                <div class="row mb-2">
                                    <div class="col-sm-4 text-muted fw-semibold">Akun SantriFit</div>
                                    <div class="col-sm-8 fw-bold text-dark"><?= htmlspecialchars($data['user']['username']); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 text-muted fw-semibold">Username Telegram</div>
                                    <div class="col-sm-8">
                                        <?php if (!empty($data['telegram_account']['telegram_username'])) : ?>
                                            <span class="badge bg-primary fs-6 fw-normal">@<?= htmlspecialchars($data['telegram_account']['telegram_username']); ?></span>
                                        <?php else : ?>
                                            <span class="text-muted fst-italic">Tanpa username publik</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 text-muted fw-semibold">Status Verifikasi</div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Terverifikasi</span>
                                    </div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-sm-4 text-muted fw-semibold">Waktu Terhubung</div>
                                    <div class="col-sm-8 text-muted">
                                        <?= date('d M Y, H:i', strtotime($data['telegram_account']['created_at'] ?? 'now')); ?> WIB
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <form action="<?= BASEURL; ?>/profile/test_notification" method="POST">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-bell me-1"></i> Kirim Notifikasi Uji Coba
                                </button>
                            </form>
                            <form action="<?= BASEURL; ?>/profile/unlink" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan hubungan akun Telegram dari SantriFit?');">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-link-45deg me-1"></i> Putuskan Hubungan (Unlink)
                                </button>
                            </form>
                        </div>

                    <?php else : ?>
                        <!-- ================= AKUN BELUM TERHUBUNG ================= -->
                        <div class="alert alert-secondary d-flex align-items-center border-0 p-3 mb-4" role="alert">
                            <i class="bi bi-info-circle-fill fs-2 me-3 text-secondary"></i>
                            <div>
                                <h5 class="alert-heading fw-bold mb-1">Status: Belum Terhubung</h5>
                                <p class="mb-0 small">Hubungkan akun Anda dengan bot Telegram SantriFit untuk verifikasi identitas pengguna.</p>
                            </div>
                        </div>

                        <?php if (empty($data['active_token'])) : ?>
                            <!-- Belum ada token aktif -->
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="bi bi-qr-code-scan text-primary" style="font-size: 3.5rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Mulai Proses Penghubungan Akun</h5>
                                <p class="text-muted small mx-auto mb-4" style="max-width: 500px;">
                                    Klik tombol di bawah untuk membuat kode linking sementara. Kode ini akan Anda kirimkan ke Telegram Bot SantriFit untuk memverifikasi akun Anda.
                                </p>
                                <form action="<?= BASEURL; ?>/profile/generate_token" method="POST">
                                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                                        <i class="bi bi-key me-1"></i> Buat Kode Linking Telegram
                                    </button>
                                </form>
                            </div>

                        <?php else : ?>
                            <!-- Token aktif tersedia -->
                            <div class="card border-primary bg-primary bg-opacity-10 mb-4">
                                <div class="card-body text-center p-4">
                                    <span class="badge bg-primary text-uppercase mb-2 px-3 py-1">Kode Linking Aktif</span>
                                    <div class="display-5 font-monospace fw-bold text-primary my-2 user-select-all letter-spacing-1">
                                        <?= htmlspecialchars($data['active_token']['token']); ?>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-clock-history me-1"></i> Berlaku hingga: 
                                        <strong><?= date('H:i:s', strtotime($data['active_token']['expires_at'])); ?> WIB</strong> (15 menit)
                                    </p>

                                    <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                                        <?php if (!empty($data['bot_username'])) : ?>
                                            <a href="https://t.me/<?= htmlspecialchars($data['bot_username']); ?>?start=<?= htmlspecialchars($data['active_token']['token']); ?>" target="_blank" class="btn btn-primary px-3 shadow-sm">
                                                <i class="bi bi-telegram me-1"></i> Buka Telegram & Hubungkan Otomatis
                                            </a>
                                        <?php endif; ?>
                                        <form action="<?= BASEURL; ?>/profile/generate_token" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-outline-secondary px-3">
                                                <i class="bi bi-arrow-clockwise me-1"></i> Buat Kode Baru
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Petunjuk Langkah -->
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-secondary text-uppercase small">Langkah Menghubungkan:</h6>
                                    <ol class="mb-0 ps-3 small text-muted lh-lg">
                                        <li>Buka aplikasi Telegram pada ponsel atau komputer Anda.</li>
                                        <li>Cari bot SantriFit: <strong class="text-dark">@<?= htmlspecialchars($data['bot_username'] ?: 'SantriFitHealthBot'); ?></strong> atau klik tombol biru di atas.</li>
                                        <li>Kirim pesan berisi kode <code class="fw-bold fs-6 text-primary"><?= htmlspecialchars($data['active_token']['token']); ?></code> ke bot.</li>
                                        <li>Bot Telegram akan memvalidasi token dan mengonfirmasi bahwa akun Anda telah terhubung.</li>
                                        <li>Halaman ini akan otomatis diperbarui begitu verifikasi selesai.</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Script Polling Status Otomatis -->
                            <script>
                            (function() {
                                var checkInterval = setInterval(function() {
                                    fetch('<?= BASEURL; ?>/profile/telegram_status')
                                        .then(function(res) { return res.json(); })
                                        .then(function(data) {
                                            if (data && data.is_linked) {
                                                clearInterval(checkInterval);
                                                window.location.reload();
                                            }
                                        })
                                        .catch(function(err) {});
                                }, 3000);
                            })();
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
