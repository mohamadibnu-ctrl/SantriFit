<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-heart-pulse-fill text-teal" style="font-size: 3rem;"></i>
                        <h2 class="fw-bold text-teal mt-2">SantriFit</h2>
                        <p class="text-muted">Kesehatan Pesantren Terpadu</p>
                    </div>
                    
                    <form action="<?= BASEURL; ?>/auth/login" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control form-control-lg border-start-0 ps-0" id="username" name="username" placeholder="Masukkan username" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" class="form-control form-control-lg border-start-0 ps-0" id="password" name="password" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-teal btn-lg fw-bold"><i class="bi bi-box-arrow-in-right"></i> Masuk Sistem</button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>
