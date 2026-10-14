<?php
$isEdit = isset($data['pemeriksaan']);
$actionUrl = $isEdit ? BASEURL . '/pemeriksaan/prosesEdit' : BASEURL . '/pemeriksaan/prosesTambah';
$p = $isEdit ? $data['pemeriksaan'] : [];
$gejala_ids = $data['gejala_ids'] ?? [];

$tanggal = $p['tanggal_pemeriksaan'] ?? date('Y-m-d\TH:i');
if ($isEdit) {
    $tanggal = date('Y-m-d\TH:i', strtotime($tanggal));
}
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-file-medical"></i> <?= $data['judul']; ?></h2>
        <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="<?= $actionUrl; ?>" method="post">
                
                <?php if($isEdit) : ?>
                    <input type="hidden" name="id" value="<?= $p['id']; ?>">
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="santri_id" class="form-label fw-semibold">Pasien (Santri) *</label>
                        <select class="form-select" id="santri_id" name="santri_id" required <?= $isEdit ? 'disabled' : ''; ?>>
                            <option value="">-- Pilih Santri --</option>
                            <?php foreach($data['santri'] as $s) : ?>
                                <option value="<?= $s['id']; ?>" <?= (isset($p['santri_id']) && $p['santri_id'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($s['nis']); ?> - <?= htmlspecialchars($s['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if($isEdit) : ?>
                            <input type="hidden" name="santri_id" value="<?= $p['santri_id']; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_pemeriksaan" class="form-label fw-semibold">Waktu Pemeriksaan *</label>
                        <input type="datetime-local" class="form-control" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan" value="<?= $tanggal; ?>" required>
                    </div>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-activity"></i> Triase (Tanda-Tanda Vital)</h5>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label for="suhu_tubuh" class="form-label text-muted small text-uppercase fw-bold">Suhu (°C)</label>
                        <input type="number" step="0.1" class="form-control" id="suhu_tubuh" name="suhu_tubuh" value="<?= $p['suhu_tubuh'] ?? ''; ?>" placeholder="36.5">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tekanan_darah" class="form-label text-muted small text-uppercase fw-bold">Tekanan Darah</label>
                        <input type="text" class="form-control" id="tekanan_darah" name="tekanan_darah" value="<?= htmlspecialchars($p['tekanan_darah'] ?? ''); ?>" placeholder="120/80">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="berat_badan" class="form-label text-muted small text-uppercase fw-bold">Berat Badan (kg)</label>
                        <input type="number" step="0.1" class="form-control" id="berat_badan" name="berat_badan" value="<?= $p['berat_badan'] ?? ''; ?>" placeholder="55">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tinggi_badan" class="form-label text-muted small text-uppercase fw-bold">Tinggi Badan (cm)</label>
                        <input type="number" class="form-control" id="tinggi_badan" name="tinggi_badan" value="<?= $p['tinggi_badan'] ?? ''; ?>" placeholder="165">
                    </div>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-chat-left-dots"></i> Keluhan & Gejala</h5>
                <div class="mb-3">
                    <label for="keluhan_utama" class="form-label fw-semibold">Keluhan Utama *</label>
                    <textarea class="form-control" id="keluhan_utama" name="keluhan_utama" rows="3" required><?= htmlspecialchars($p['keluhan_utama'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Gejala-Gejala Terkait</label>
                    <div class="row">
                        <?php foreach($data['gejala_master'] as $gm) : ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="gejala[]" value="<?= $gm['id']; ?>" id="gejala_<?= $gm['id']; ?>" <?= in_array($gm['id'], $gejala_ids) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="gejala_<?= $gm['id']; ?>" title="<?= htmlspecialchars($gm['deskripsi']); ?>">
                                    <?= htmlspecialchars($gm['nama_gejala']); ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <h5 class="mb-3 text-teal border-bottom pb-2 mt-5"><i class="bi bi-clipboard2-check"></i> Penanganan Medis</h5>
                <div class="mb-3">
                    <label for="hasil_pemeriksaan" class="form-label fw-semibold">Hasil Pemeriksaan Fisik / Diagnosa Sementara</label>
                    <textarea class="form-control" id="hasil_pemeriksaan" name="hasil_pemeriksaan" rows="3"><?= htmlspecialchars($p['hasil_pemeriksaan'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="catatan_petugas" class="form-label fw-semibold">Tindakan / Catatan Petugas</label>
                    <textarea class="form-control" id="catatan_petugas" name="catatan_petugas" rows="2"><?= htmlspecialchars($p['catatan_petugas'] ?? ''); ?></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold">Status Pemeriksaan *</label>
                        <select class="form-select font-weight-bold" id="status" name="status" required>
                            <option value="Menunggu Dokter" <?= (isset($p['status']) && $p['status'] == 'Menunggu Dokter') ? 'selected' : ''; ?>>Menunggu Dokter</option>
                            <option value="Sedang Ditangani" <?= (isset($p['status']) && $p['status'] == 'Sedang Ditangani') ? 'selected' : ''; ?>>Sedang Ditangani</option>
                            <option value="Perlu Tindak Lanjut" <?= (isset($p['status']) && $p['status'] == 'Perlu Tindak Lanjut') ? 'selected' : ''; ?>>Perlu Tindak Lanjut (Rujuk/Observasi)</option>
                            <option value="Selesai" <?= (isset($p['status']) && $p['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5 pt-3 border-top">
                    <button type="submit" class="btn btn-teal px-5 py-2 fw-bold"><i class="bi bi-save"></i> <?= $isEdit ? 'Simpan Update' : 'Catat Pemeriksaan'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
