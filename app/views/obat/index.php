<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-capsule"></i> Data Obat</h2>
        <?php if(in_array('Super User', $_SESSION['user_roles'])) : ?>
            <a href="<?= BASEURL; ?>/obat/tambah" class="btn btn-teal"><i class="bi bi-plus-circle"></i> Tambah Obat</a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Obat</th>
                            <th>Nama Obat</th>
                            <th>Satuan</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <?php if(in_array('Super User', $_SESSION['user_roles'])) : ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($data['obat'] as $o) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($o['kode_obat'] ?: '-'); ?></td>
                            <td><?= htmlspecialchars($o['nama_obat']); ?></td>
                            <td><?= htmlspecialchars($o['satuan'] ?: '-'); ?></td>
                            <td>
                                <?php if($o['stok'] > 10) : ?>
                                    <span class="badge bg-success"><?= $o['stok']; ?></span>
                                <?php elseif($o['stok'] > 0) : ?>
                                    <span class="badge bg-warning text-dark"><?= $o['stok']; ?></span>
                                <?php else : ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $o['status_aktif'] == '1' ? '<span class="badge bg-teal">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>'; ?>
                            </td>
                            <?php if(in_array('Super User', $_SESSION['user_roles'])) : ?>
                            <td>
                                <a href="<?= BASEURL; ?>/obat/edit/<?= $o['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
