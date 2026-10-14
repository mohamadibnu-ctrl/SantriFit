<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi <?= isset($data['dokter']) ? 'bi-pencil-square' : 'bi-plus-lg'; ?>"></i> <?= $data['judul']; ?></h2>
        <a href="<?= BASEURL; ?>/dokter" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= BASEURL; ?>/dokter/<?= isset($data['dokter']) ? 'prosesEdit' : 'prosesTambah'; ?>" method="post">
                <?php if(isset($data['dokter'])) : ?>
                    <input type="hidden" name="id" value="<?= $data['dokter']['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nama" class="form-label fw-semibold">Nama Dokter *</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= isset($data['dokter']) ? $data['dokter']['nama'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="no_izin_praktek" class="form-label fw-semibold">No. Izin Praktek</label>
                    <input type="text" class="form-control" id="no_izin_praktek" name="no_izin_praktek" value="<?= isset($data['dokter']) ? $data['dokter']['no_izin_praktek'] : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="spesialisasi" class="form-label fw-semibold">Spesialisasi</label>
                    <input type="text" class="form-control" id="spesialisasi" name="spesialisasi" value="<?= isset($data['dokter']) ? $data['dokter']['spesialisasi'] : ''; ?>" placeholder="Contoh: Umum, Penyakit Dalam, dll.">
                </div>
                
                <div class="mb-4">
                    <label for="user_id" class="form-label fw-semibold">Akun Pengguna (User) *</label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        <option value="">-- Pilih Akun Pengguna --</option>
                        <?php foreach($data['users'] as $user) : ?>
                            <option value="<?= $user['id']; ?>" <?= (isset($data['dokter']) && $data['dokter']['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Pilih akun pengguna (login) yang akan dihubungkan dengan profil dokter ini.</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-teal px-4 fw-bold"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
