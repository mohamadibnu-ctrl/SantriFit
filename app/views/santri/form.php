<?php
$isEdit = isset($data['santri']);
$actionUrl = $isEdit ? BASEURL . '/santri/prosesEdit' : BASEURL . '/santri/prosesTambah';
$santri = $isEdit ? $data['santri'] : [];
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-person-badge"></i> <?= $data['judul']; ?></h2>
        <a href="<?= BASEURL; ?>/santri" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= $actionUrl; ?>" method="post" enctype="multipart/form-data">
                
                <?php if($isEdit) : ?>
                    <input type="hidden" name="id" value="<?= $santri['id']; ?>">
                    <input type="hidden" name="foto_lama" value="<?= $santri['foto']; ?>">
                <?php endif; ?>

                <h5 class="mb-3 text-teal border-bottom pb-2"><i class="bi bi-person-lines-fill"></i> Informasi Pribadi</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nis" class="form-label fw-semibold">NIS (Nomor Induk Santri) *</label>
                        <input type="text" class="form-control" id="nis" name="nis" value="<?= htmlspecialchars($santri['nis'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nama" class="form-label fw-semibold">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($santri['nama'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tempat_lahir" class="form-label fw-semibold">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?= htmlspecialchars($santri['tempat_lahir'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label fw-semibold">Tanggal Lahir *</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= $santri['tanggal_lahir'] ?? ''; ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin *</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" <?= (isset($santri['jenis_kelamin']) && $santri['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?= (isset($santri['jenis_kelamin']) && $santri['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="no_kontak" class="form-label fw-semibold">No. Kontak / HP</label>
                        <input type="text" class="form-control" id="no_kontak" name="no_kontak" value="<?= htmlspecialchars($santri['no_kontak'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="alamat" class="form-label fw-semibold">Alamat Lengkap</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2"><?= htmlspecialchars($santri['alamat'] ?? ''); ?></textarea>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-building"></i> Akademik & Asrama</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="kelas" class="form-label fw-semibold">Kelas</label>
                        <input type="text" class="form-control" id="kelas" name="kelas" value="<?= htmlspecialchars($santri['kelas'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="kamar" class="form-label fw-semibold">Kamar Asrama</label>
                        <input type="text" class="form-control" id="kamar" name="kamar" value="<?= htmlspecialchars($santri['kamar'] ?? ''); ?>">
                    </div>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-people"></i> Orang Tua / Wali</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="nama_wali" class="form-label fw-semibold">Nama Orang Tua/Wali</label>
                        <input type="text" class="form-control" id="nama_wali" name="nama_wali" value="<?= htmlspecialchars($santri['nama_wali'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="kontak_wali" class="form-label fw-semibold">Kontak Orang Tua/Wali</label>
                        <input type="text" class="form-control" id="kontak_wali" name="kontak_wali" value="<?= htmlspecialchars($santri['kontak_wali'] ?? ''); ?>">
                    </div>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-heart-pulse"></i> Informasi Kesehatan Pendukung</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="riwayat_penyakit" class="form-label fw-semibold">Riwayat Penyakit Penting (Jika ada)</label>
                        <textarea class="form-control" id="riwayat_penyakit" name="riwayat_penyakit" rows="2"><?= htmlspecialchars($santri['riwayat_penyakit'] ?? ''); ?></textarea>
                        <div class="form-text">Contoh: Asma, Tipes kronis.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="alergi" class="form-label fw-semibold">Alergi Makanan / Obat</label>
                        <textarea class="form-control" id="alergi" name="alergi" rows="2"><?= htmlspecialchars($santri['alergi'] ?? ''); ?></textarea>
                        <div class="form-text">Contoh: Alergi udang, amoxicillin.</div>
                    </div>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-three-dots"></i> Lain-lain</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="foto" class="form-label fw-semibold">Unggah Foto (Opsional, Maks 2MB)</label>
                        <input class="form-control" type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg">
                        <?php if($isEdit && !empty($santri['foto'])) : ?>
                            <div class="mt-2 text-muted small">
                                Foto saat ini: <?= htmlspecialchars($santri['foto']); ?> (Kosongkan jika tidak ingin mengubah foto)
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($isEdit) : ?>
                    <div class="col-md-6">
                        <label for="status_aktif" class="form-label fw-semibold">Status Santri</label>
                        <select class="form-select" id="status_aktif" name="status_aktif">
                            <option value="1" <?= ($santri['status_aktif'] == '1') ? 'selected' : ''; ?>>Aktif (Belajar)</option>
                            <option value="0" <?= ($santri['status_aktif'] == '0') ? 'selected' : ''; ?>>Nonaktif (Lulus/Pindah)</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5 pt-3 border-top">
                    <button type="submit" class="btn btn-teal px-5 py-2 fw-bold"><i class="bi bi-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambahkan Santri'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
