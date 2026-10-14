<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Instruksi Pemberian Obat (Resep Dokter)</h2>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl & Santri</th>
                            <th>Resep Obat</th>
                            <th>Instruksi (Dosis & Aturan)</th>
                            <th>Status Pemberian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['resep'] as $r) : ?>
                        <tr class="<?= $r['waktu_diberikan'] ? 'table-light' : ''; ?>">
                            <td>
                                <strong><?= $r['nama_santri']; ?></strong><br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($r['tanggal_pemeriksaan'])); ?></small>
                            </td>
                            <td>
                                <strong><?= $r['nama_obat']; ?></strong><br>
                                <small class="text-muted">Jml: <?= $r['jumlah']; ?> <?= $r['satuan']; ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $r['dosis']; ?></span><br>
                                <?= $r['aturan_minum']; ?>
                            </td>
                            <td>
                                <?php if($r['waktu_diberikan']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Sudah Diberikan</span><br>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($r['waktu_diberikan'])); ?> oleh <?= $r['nama_perawat']; ?></small>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Belum Diberikan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!$r['waktu_diberikan']): ?>
                                    <a href="<?= BASEURL; ?>/pemberian/catat/<?= $r['resep_id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Catat obat ini sudah diserahkan ke santri?')"><i class="bi bi-capsule"></i> Tandai Diberikan</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data['resep'])) : ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada instruksi pemberian obat yang perlu dikerjakan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
