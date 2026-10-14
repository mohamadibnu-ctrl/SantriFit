# SantriFit

SantriFit adalah sistem manajemen kesehatan santri berbasis web untuk lingkungan pondok pesantren.

## Tujuan Aplikasi
Sistem ini dirancang untuk mempermudah pencatatan medis, pengelolaan riwayat kesehatan santri, serta memberikan akses terintegrasi kepada Super User, Pengurus, Perawat, Dokter, dan Santri. Aplikasi ini juga dilengkapi dengan asisten AI untuk memberikan rekomendasi awal.

## Struktur Folder Utama
Aplikasi ini dibangun menggunakan arsitektur MVC (Model-View-Controller) dengan PHP murni (*native*).

```text
SantriFit/
├── app/                  # Direktori logika aplikasi
│   ├── config/           # File konfigurasi (database, base_url)
│   ├── controllers/      # Kelas Controller (penghubung view & model)
│   ├── core/             # File inti MVC (App, Controller, Database)
│   ├── middlewares/      # Middleware autentikasi
│   ├── models/           # Kelas Model (akses data)
│   └── views/            # Template tampilan UI (HTML/PHP)
├── public/               # Direktori publik yang dapat diakses browser
│   ├── css/              # File style kustom
│   ├── img/              # Gambar aplikasi
│   ├── js/               # Skrip JavaScript kustom
│   ├── .htaccess         # Aturan routing untuk public folder
│   └── index.php         # Entry point aplikasi
├── .htaccess             # Redirect root ke public folder
└── README.md             # Dokumentasi aplikasi
```

## Cara Menjalankan Aplikasi

1. **Pastikan Web Server (XAMPP/MAMP/WAMP) berjalan** (Apache dan MySQL).
2. Pindahkan atau *clone* direktori ini ke dalam folder `htdocs` (jika menggunakan XAMPP) dengan nama folder `SantriFit`.
3. Buka *browser* dan akses URL: `http://localhost/SantriFit/`
4. Aplikasi akan secara otomatis diarahkan ke `public/index.php`.

## Konfigurasi Database

Buka file `app/config/config.php` untuk mengatur kredensial database. Secara default:
- DB_HOST: localhost
- DB_USER: root
- DB_PASS: (kosong)
- DB_NAME: santrifit_db

*Catatan: Struktur tabel (skema database) akan dibuat pada iterasi berikutnya.*

## Akun Development (Akan Datang)
(Informasi akun untuk testing/development belum disetup karena fokus pengembangan masih pada struktur fondasi. Akan diupdate kemudian).
