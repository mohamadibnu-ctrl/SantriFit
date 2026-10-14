<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['judul']; ?> - SantriFit</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASEURL; ?>/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-teal shadow-sm mb-4 py-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASEURL; ?>">SantriFit</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <?php if(isset($_SESSION['user_id'])) : ?>
            <li class="nav-item">
              <a class="nav-link <?= (isset($data['judul']) && $data['judul'] == 'Beranda SantriFit') ? 'active' : '' ?>" href="<?= BASEURL; ?>">Dashboard</a>
            </li>
            
            <?php $roles = $_SESSION['user_roles'] ?? []; ?>
            
            <?php if(in_array('Super User', $roles) || in_array('Pengurus', $roles) || in_array('Perawat', $roles)) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/santri">Data Santri</a>
            </li>
            <?php endif; ?>
            
            <?php if(in_array('Super User', $roles) || in_array('Perawat', $roles) || in_array('Dokter', $roles)) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/pemeriksaan">Triase & Pemeriksaan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/rekomendasi">Rekomendasi & Resep</a>
            </li>
            <?php endif; ?>
            
            <?php if(in_array('Super User', $roles) || in_array('Perawat', $roles)) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/pemberian">Pemberian Obat</a>
            </li>
            <?php endif; ?>

            <?php if(in_array('Super User', $roles) || in_array('Dokter', $roles) || in_array('Perawat', $roles)) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/obat">Data Obat</a>
            </li>
            <?php endif; ?>
            
            <?php if(in_array('Super User', $roles) || in_array('Pengurus', $roles) || in_array('Dokter', $roles) || in_array('Perawat', $roles)) : ?>
            <li class="nav-item">
              <a class="nav-link <?= (isset($data['judul']) && $data['judul'] == 'Laporan Kesehatan') ? 'active' : '' ?>" href="<?= BASEURL; ?>/laporan"><i class="bi bi-graph-up"></i> Laporan</a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
              <a class="nav-link <?= (isset($data['judul']) && $data['judul'] == 'Asisten AI SantriFit') ? 'active' : '' ?>" href="<?= BASEURL; ?>/aiassistant"><i class="bi bi-robot"></i> Asisten AI</a>
            </li>

            <?php if(in_array('Super User', $roles)) : ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/dokter">Data Dokter</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASEURL; ?>/user">Data Pengguna</a>
            </li>
            <?php endif; ?>
            
        <?php else : ?>
            <li class="nav-item">
              <a class="nav-link <?= (isset($data['judul']) && $data['judul'] == 'Beranda SantriFit') ? 'active' : '' ?>" aria-current="page" href="<?= BASEURL; ?>">Beranda</a>
            </li>
        <?php endif; ?>
      </ul>
      
      <ul class="navbar-nav ms-auto">
        <?php if(isset($_SESSION['user_id'])) : ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Hi, <?= $_SESSION['username']; ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="<?= BASEURL; ?>/profile">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= BASEURL; ?>/auth/logout">Logout</a></li>
              </ul>
            </li>
        <?php else : ?>
            <li class="nav-item">
              <a class="nav-link <?= (isset($data['judul']) && $data['judul'] == 'Login') ? 'active' : '' ?>" href="<?= BASEURL; ?>/auth/index">Login</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <?php Flasher::flash(); ?>
</div>
