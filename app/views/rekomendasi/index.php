<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-journal-medical"></i> Data Rekomendasi Dokter</h2>
        <?php if(in_array('Dokter', $_SESSION['user_roles'])) : ?>
            <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-teal"><i class="bi bi-clipboard2-pulse"></i> Pilih Pasien dari Triase</a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl Periksa</th>
                            <th>Nama Pasien</th>
                            <th>Dokter Pemeriksa</th>
                            <th>Status Rekomendasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['rekomendasi'] as $r) : ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal_pemeriksaan'])); ?></td>
                            <td><?= htmlspecialchars($r['nama_santri']); ?> <small class="text-muted">(<?= htmlspecialchars($r['nis']); ?>)</small></td>
                            <td><?= htmlspecialchars($r['nama_dokter']); ?></td>
                            <td>
                                <?= $r['status'] == 'Final' ? '<span class="badge bg-success">Final</span>' : '<span class="badge bg-warning text-dark">Draft</span>'; ?>
                            </td>
                            <td>
                                <a href="<?= BASEURL; ?>/rekomendasi/form/<?= $r['pemeriksaan_id']; ?>" class="btn btn-sm btn-outline-info" title="Buka"><i class="bi bi-eye"></i> Buka</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($data['rekomendasi'])) : ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data rekomendasi dokter.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
