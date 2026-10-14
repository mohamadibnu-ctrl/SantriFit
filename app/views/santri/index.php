<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-people-fill"></i> Data Santri</h2>
        <a href="<?= BASEURL; ?>/santri/tambah" class="btn btn-teal"><i class="bi bi-person-plus-fill"></i> Tambah Santri</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="<?= BASEURL; ?>/santri" method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari NIS atau Nama..." value="<?= htmlspecialchars($data['keyword']); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" <?= $data['status'] === '1' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= $data['status'] === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="<?= BASEURL; ?>/santri" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
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
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>L/P</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = ($data['currentPage'] - 1) * 10 + 1;
                        foreach($data['santri'] as $santri) : 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($santri['nis']); ?></td>
                            <td><?= htmlspecialchars($santri['nama']); ?></td>
                            <td><?= htmlspecialchars($santri['jenis_kelamin']); ?></td>
                            <td><?= htmlspecialchars($santri['kelas'] ?? '-'); ?></td>
                            <td>
                                <?php if($santri['status_aktif'] == '1') : ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else : ?>
                                    <span class="badge bg-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASEURL; ?>/santri/detail/<?= $santri['id']; ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="<?= BASEURL; ?>/santri/edit/<?= $santri['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if($santri['status_aktif'] == '1') : ?>
                                    <a href="<?= BASEURL; ?>/santri/nonaktifkan/<?= $santri['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menonaktifkan santri ini? Data medis akan tetap tersimpan.');" title="Nonaktifkan"><i class="bi bi-person-x"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($data['santri'])) : ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Data santri tidak ditemukan.</td>
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
                    <a class="page-link" href="<?= BASEURL; ?>/santri?page=<?= $i; ?>&q=<?= $data['keyword']; ?>&status=<?= $data['status']; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
