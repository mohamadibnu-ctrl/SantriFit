<?php
$p = $data['pemeriksaan'];
$rek = $data['rekomendasi'] ?? [];
$isFinal = isset($rek['status']) && $rek['status'] == 'Final';
?>
<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><?= $data['judul']; ?></h2>
        <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-secondary">Kembali</a>
    </div>

    <!-- Info Pasien -->
    <div class="card mb-4 border-0 shadow-sm border-start border-primary border-4">
        <div class="card-body">
            <h5 class="fw-bold">Pasien: <?= $p['nama_santri']; ?> (<?= $p['nis']; ?>)</h5>
            <p class="mb-1 text-muted">Tanggal Periksa: <?= date('d M Y H:i', strtotime($p['tanggal_pemeriksaan'])); ?></p>
            <p class="mb-1"><strong>Keluhan:</strong> <?= htmlspecialchars($p['keluhan_utama']); ?></p>
            <p class="mb-0"><strong>Hasil Triase:</strong> Tensi <?= $p['tekanan_darah']?:'-'; ?> | Suhu <?= $p['suhu_tubuh']?:'-'; ?>°C | BB <?= $p['berat_badan']?:'-'; ?>kg</p>
        </div>
    </div>

    <?php if($isFinal): ?>
        <div class="alert alert-info shadow-sm border-0"><i class="bi bi-lock-fill"></i> Rekomendasi ini sudah <strong>FINAL</strong> dan tidak dapat diubah.</div>
    <?php endif; ?>

    <div class="row">
        <!-- Form Rekomendasi -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white pt-3 pb-0 border-0">
                    <h5 class="fw-bold">1. Diagnosa & Rekomendasi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASEURL; ?>/rekomendasi/simpan" method="post" id="formRekomendasi">
                        <input type="hidden" name="pemeriksaan_id" value="<?= $p['id']; ?>">
                        <input type="hidden" name="santri_id" value="<?= $p['santri_id']; ?>">
                        <?php if(!empty($rek)): ?>
                            <input type="hidden" name="id" value="<?= $rek['id']; ?>">
                        <?php endif; ?>

                        <!-- For passing status of pemeriksaan if final -->
                        <input type="hidden" name="tanggal_pemeriksaan" value="<?= $p['tanggal_pemeriksaan']; ?>">
                        <input type="hidden" name="keluhan_utama" value="<?= htmlspecialchars($p['keluhan_utama']); ?>">
                        <input type="hidden" name="suhu_tubuh" value="<?= $p['suhu_tubuh']; ?>">
                        <input type="hidden" name="tekanan_darah" value="<?= $p['tekanan_darah']; ?>">
                        <input type="hidden" name="berat_badan" value="<?= $p['berat_badan']; ?>">
                        <input type="hidden" name="tinggi_badan" value="<?= $p['tinggi_badan']; ?>">
                        <input type="hidden" name="hasil_pemeriksaan" value="<?= $p['hasil_pemeriksaan']; ?>">
                        <input type="hidden" name="catatan_petugas" value="<?= $p['catatan_petugas']; ?>">

                        <div class="mb-3">
                            <label class="form-label text-muted small">Diagnosis Medis *</label>
                            <textarea class="form-control" name="diagnosis_medis" rows="3" required <?= $isFinal?'disabled':''; ?>><?= $rek['diagnosis_medis'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small">Catatan Tambahan / Saran</label>
                            <textarea class="form-control" name="catatan_saran" rows="3" <?= $isFinal?'disabled':''; ?>><?= $rek['catatan_saran'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small">Status Rekomendasi</label>
                            <select name="status" class="form-select" <?= $isFinal?'disabled':''; ?>>
                                <option value="Draft" <?= (!isset($rek['status']) || $rek['status'] == 'Draft') ? 'selected' : ''; ?>>Draft (Bisa diedit)</option>
                                <option value="Final" <?= (isset($rek['status']) && $rek['status'] == 'Final') ? 'selected' : ''; ?>>Final (Kunci data & Selesaikan Pemeriksaan)</option>
                            </select>
                            <?php if($isFinal): ?>
                                <input type="hidden" name="status" value="Final">
                            <?php endif; ?>
                        </div>
                        
                        <?php if(!$isFinal): ?>
                        <button type="submit" class="btn btn-primary w-100" id="btnSimpanRek">Simpan Rekomendasi</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Resep -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white pt-3 pb-0 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold">2. Resep Obat</h5>
                    <?php if(!$isFinal && !empty($rek)): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahResep"><i class="bi bi-plus"></i> Obat Lain</button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if(empty($rek)): ?>
                        <div class="alert alert-warning border-0">Silakan simpan <strong>Diagnosa</strong> di form sebelah kiri terlebih dahulu sebelum menambahkan resep obat.</div>
                    <?php else: ?>
                        <!-- Daftar Resep Existing -->
                        <?php if(!empty($data['resep'])): ?>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light text-center small">
                                        <tr>
                                            <th>Obat</th>
                                            <th>Dosis</th>
                                            <th>Aturan</th>
                                            <th>Jumlah</th>
                                            <?php if(!$isFinal): ?><th>Aksi</th><?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="small align-middle text-center">
                                        <?php foreach($data['resep'] as $r): ?>
                                            <tr>
                                                <td class="text-start"><strong><?= $r['nama_obat']; ?></strong><br><span class="text-muted"><?= $r['satuan']; ?></span></td>
                                                <td><?= $r['dosis']; ?></td>
                                                <td><?= $r['aturan_minum']; ?></td>
                                                <td><?= $r['jumlah']; ?></td>
                                                <?php if(!$isFinal): ?>
                                                <td>
                                                    <a href="<?= BASEURL; ?>/rekomendasi/hapus_resep/<?= $r['id']; ?>/<?= $p['id']; ?>" class="btn btn-sm btn-danger py-0 px-2" onclick="return confirm('Hapus obat ini dari resep?')"><i class="bi bi-x"></i></a>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted fst-italic mb-4">Belum ada obat yang diresepkan.</p>
                        <?php endif; ?>

                        <!-- Form Tambah Resep Multiple -->
                        <?php if(!$isFinal): ?>
                        <form action="<?= BASEURL; ?>/rekomendasi/simpan" method="post" id="formResep">
                            <input type="hidden" name="id" value="<?= $rek['id']; ?>">
                            <input type="hidden" name="pemeriksaan_id" value="<?= $p['id']; ?>">
                            <input type="hidden" name="diagnosis_medis" value="<?= htmlspecialchars($rek['diagnosis_medis']); ?>">
                            <input type="hidden" name="catatan_saran" value="<?= htmlspecialchars($rek['catatan_saran']); ?>">
                            <input type="hidden" name="status" value="<?= $rek['status']; ?>">
                            
                            <div id="resepContainer">
                                <!-- Template Obat 1 -->
                                <div class="row g-2 align-items-end resep-row p-3 border rounded bg-light mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted mb-1">Pilih Obat</label>
                                        <select name="obat_id[]" class="form-select form-select-sm select-obat">
                                            <option value="">- Kosong -</option>
                                            <?php foreach($data['obat_master'] as $o): ?>
                                                <option value="<?= $o['id']; ?>"><?= $o['nama_obat']; ?> (<?= $o['stok']; ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Dosis</label>
                                        <input type="text" name="dosis[]" class="form-control form-control-sm" placeholder="ex: 500mg">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Aturan Minum</label>
                                        <input type="text" name="aturan_minum[]" class="form-control form-control-sm" placeholder="ex: 3x1 sesudah makan">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Qty</label>
                                        <input type="number" name="jumlah[]" class="form-control form-control-sm" placeholder="Jml">
                                        <input type="hidden" name="durasi_hari[]" value="">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-hapus-row" disabled><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success mt-3 w-100"><i class="bi bi-bag-plus"></i> Tambahkan ke Resep & Simpan</button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTambah = document.getElementById('btnTambahResep');
    const container = document.getElementById('resepContainer');
    
    if(btnTambah && container) {
        btnTambah.addEventListener('click', function() {
            const firstRow = container.querySelector('.resep-row');
            if(firstRow) {
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input, select').forEach(el => {
                    if(el.type !== 'hidden') el.value = '';
                });
                const delBtn = newRow.querySelector('.btn-hapus-row');
                delBtn.disabled = false;
                delBtn.addEventListener('click', function() {
                    newRow.remove();
                });
                container.appendChild(newRow);
            }
        });
    }
});
</script>
