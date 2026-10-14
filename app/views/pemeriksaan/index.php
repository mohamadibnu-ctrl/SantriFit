<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-clipboard2-pulse"></i> Data Pemeriksaan Medis</h2>
        <?php if(in_array('Perawat', $_SESSION['user_roles']) || in_array('Super User', $_SESSION['user_roles']) || in_array('Dokter', $_SESSION['user_roles'])) : ?>
            <a href="<?= BASEURL; ?>/pemeriksaan/tambah" class="btn btn-teal"><i class="bi bi-plus-circle"></i> Pemeriksaan Baru</a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= BASEURL; ?>/pemeriksaan" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tanggal']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Pasien / Santri</label>
                    <select name="santri_id" class="form-select form-select-sm">
                        <option value="">Semua Santri</option>
                        <?php foreach($data['semua_santri'] as $s) : ?>
                            <option value="<?= $s['id']; ?>" <?= ($data['santri_id'] == $s['id']) ? 'selected' : ''; ?>><?= $s['nama']; ?> (<?= $s['nis']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status Pemeriksaan</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="Menunggu Dokter" <?= ($data['status'] === 'Menunggu Dokter') ? 'selected' : ''; ?>>Menunggu Dokter</option>
                        <option value="Sedang Ditangani" <?= ($data['status'] === 'Sedang Ditangani') ? 'selected' : ''; ?>>Sedang Ditangani</option>
                        <option value="Perlu Tindak Lanjut" <?= ($data['status'] === 'Perlu Tindak Lanjut') ? 'selected' : ''; ?>>Perlu Tindak Lanjut</option>
                        <option value="Selesai" <?= ($data['status'] === 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-secondary w-100">Filter</button>
                    <a href="<?= BASEURL; ?>/pemeriksaan" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl & Waktu</th>
                            <th>Nama Pasien</th>
                            <th>Petugas (Triase)</th>
                            <th>Keluhan Utama</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['pemeriksaan'] as $p) : ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($p['tanggal_pemeriksaan'])); ?></td>
                            <td>
                                <a href="<?= BASEURL; ?>/pemeriksaan/riwayat/<?= $p['santri_id']; ?>" class="text-decoration-none fw-bold text-primary" title="Lihat Riwayat Medis"><?= htmlspecialchars($p['nama_santri']); ?></a><br>
                                <small class="text-muted"><?= htmlspecialchars($p['nis']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($p['nama_petugas']); ?></td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($p['keluhan_utama']); ?>">
                                    <?= htmlspecialchars($p['keluhan_utama']); ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                    $badge = 'bg-secondary';
                                    if ($p['status'] == 'Menunggu Dokter') $badge = 'bg-warning text-dark';
                                    if ($p['status'] == 'Sedang Ditangani') $badge = 'bg-primary';
                                    if ($p['status'] == 'Perlu Tindak Lanjut') $badge = 'bg-danger';
                                    if ($p['status'] == 'Selesai') $badge = 'bg-success';
                                ?>
                                <span class="badge <?= $badge; ?>"><?= $p['status']; ?></span>
                            </td>
                            <td>
                                <a href="<?= BASEURL; ?>/pemeriksaan/detail/<?= $p['id']; ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                <?php if(in_array('Dokter', $_SESSION['user_roles']) || in_array('Super User', $_SESSION['user_roles']) || (in_array('Perawat', $_SESSION['user_roles']))) : ?>
                                    <a href="<?= BASEURL; ?>/pemeriksaan/edit/<?= $p['id']; ?>" class="btn btn-sm btn-outline-warning" title="Update"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>
                                <?php if(in_array('Dokter', $_SESSION['user_roles']) || in_array('Super User', $_SESSION['user_roles'])) : ?>
                                    <a href="<?= BASEURL; ?>/rekomendasi/form/<?= $p['id']; ?>" class="btn btn-sm btn-outline-primary" title="Buat Rekomendasi/Resep"><i class="bi bi-prescription2"></i> Resep</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data['pemeriksaan'])) : ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada catatan pemeriksaan sesuai filter.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if($data['totalPages'] > 1) : ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for($i=1; $i<=$data['totalPages']; $i++) : ?>
                <li class="page-item <?= ($i == $data['currentPage']) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?= BASEURL; ?>/pemeriksaan?page=<?= $i; ?>&tanggal=<?= $data['tanggal']; ?>&santri_id=<?= $data['santri_id']; ?>&status=<?= $data['status']; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
