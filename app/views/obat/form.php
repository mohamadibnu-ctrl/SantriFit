<?php
$isEdit = isset($data['obat']);
$actionUrl = $isEdit ? BASEURL . '/obat/prosesEdit' : BASEURL . '/obat/prosesTambah';
$o = $isEdit ? $data['obat'] : [];
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-capsule"></i> <?= $data['judul']; ?></h2>
        <a href="<?= BASEURL; ?>/obat" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= $actionUrl; ?>" method="post">
                <?php if($isEdit) : ?>
                    <input type="hidden" name="id" value="<?= $o['id']; ?>">
                <?php endif; ?>

                <div class="row mb-4 mt-2">
                    <div class="col-md-6">
                        <label for="kode_obat" class="form-label fw-semibold">Kode Obat</label>
                        <input type="text" class="form-control" id="kode_obat" name="kode_obat" value="<?= htmlspecialchars($o['kode_obat'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="nama_obat" class="form-label fw-semibold">Nama Obat *</label>
                        <input type="text" class="form-control" id="nama_obat" name="nama_obat" value="<?= htmlspecialchars($o['nama_obat'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="satuan" class="form-label fw-semibold">Satuan (Strip, Botol, Tablet, dll)</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" value="<?= htmlspecialchars($o['satuan'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="stok" class="form-label fw-semibold">Stok Awal</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?= $o['stok'] ?? '0'; ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="deskripsi" class="form-label fw-semibold">Deskripsi Obat</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($o['deskripsi'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="status_aktif" class="form-label fw-semibold">Status</label>
                    <select class="form-select" id="status_aktif" name="status_aktif">
                        <option value="1" <?= (isset($o['status_aktif']) && $o['status_aktif'] == '1') ? 'selected' : ''; ?>>Aktif (Tersedia untuk Diresepkan)</option>
                        <option value="0" <?= (isset($o['status_aktif']) && $o['status_aktif'] == '0') ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5 pt-3 border-top">
                    <button type="submit" class="btn btn-teal px-5 py-2 fw-bold"><i class="bi bi-save"></i> <?= $isEdit ? 'Simpan Data' : 'Tambahkan Obat'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
