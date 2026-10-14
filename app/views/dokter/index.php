<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-teal"><i class="bi bi-person-heart"></i> Data Dokter</h2>
        <a href="<?= BASEURL; ?>/dokter/tambah" class="btn btn-teal fw-bold"><i class="bi bi-plus-lg"></i> Tambah Dokter</a>
    </div>

    <?php Flasher::flash(); ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" width="5%">No</th>
                            <th scope="col">Nama Dokter</th>
                            <th scope="col">Spesialisasi</th>
                            <th scope="col">No. Izin Praktek</th>
                            <th scope="col" class="text-center" width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['dokter'])) : ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data dokter.</td>
                        </tr>
                        <?php else : ?>
                            <?php $no = 1; foreach($data['dokter'] as $d) : ?>
                            <tr>
                                <th scope="row"><?= $no++; ?></th>
                                <td class="fw-semibold"><?= htmlspecialchars($d['nama']); ?></td>
                                <td><span class="badge bg-teal-soft text-teal"><?= htmlspecialchars($d['spesialisasi'] ?? 'Umum'); ?></span></td>
                                <td><?= htmlspecialchars($d['no_izin_praktek'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <a href="<?= BASEURL; ?>/dokter/edit/<?= $d['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
