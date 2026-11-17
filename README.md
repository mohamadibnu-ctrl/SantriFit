# SantriFit – Sistem Informasi Berbasis Web Dilengkapi Chatbot AI

SantriFit adalah sistem informasi berbasis web yang dilengkapi dengan Chatbot AI untuk membantu pengguna memperoleh informasi berdasarkan data yang tersedia pada sistem.

## Identitas Kelompok

- **Kelompok**: Kelompok 6
- **Kelas**: 1D 25
- **Dosen Pengampu**: Yudhistira Arie Wijaya, M.kom

## Anggota Kelompok

- **Anggota 1**: MOH NUR ARIQUL FAUZI (41257205)
- **Anggota 2**: MOHAMAD IBNU SAEFULLAH (41257206)
- **Anggota 3**: MUHAMMAD HAIDIR ALI (41257211)
- **Anggota 4**: MUHAMAD FAUZAN (41257209)
- **Anggota 5**: NUR FAJAR SIDIQ (41257220)
- **Anggota 6**: YOGA SATRIA ALFARITZI (41257231)

## Video Presentasi

[YouTube – SantriFit](https://youtu.be/Xbi6SLUu4nA?si=GmmYrsf3N6JZ4bcQ)

---

## Tujuan Aplikasi
Sistem ini dirancang untuk mempermudah pencatatan medis, pengelolaan riwayat kesehatan santri, serta memberikan akses terintegrasi kepada Super User, Pengurus, Perawat, Dokter, dan Santri. Aplikasi ini juga dilengkapi dengan asisten AI dan integrasi Bot Telegram untuk memberikan rekomendasi awal serta notifikasi terpadu.

## Fitur Utama
1. **Pemeriksaan & Triase**: Pencatatan tanda vital dan keluhan santri oleh perawat/dokter.
2. **Rekomendasi & Resep Obat**: Diagnosis dokter dan instruksi dosis obat.
3. **Data & Stok Obat**: Pengelolaan inventaris obat dan riwayat pemberian obat.
4. **Chatbot AI & Asisten Cerdas**: Konsultasi keluhan dan pencarian informasi seputar data klinik secara real-time.
5. **Integrasi Bot Telegram**: Webhook, commands (`/start`, `/help`, `/status`, `/profile`, `/unlink`), linking akun, chatbot interaktif, dan notifikasi otomatis.
6. **Laporan & Rekam Medis**: Rekapitulasi riwayat kesehatan dan grafik data medis.

## Struktur Folder Utama
Aplikasi ini dibangun menggunakan arsitektur MVC (Model-View-Controller) dengan PHP murni (*native*).

```text
SantriFit/
├── app/                  # Direktori logika aplikasi
│   ├── config/           # File konfigurasi (database, base_url, telegram)
│   ├── controllers/      # Kelas Controller (penghubung view & model)
│   ├── core/             # File inti MVC (App, Controller, Database, Flasher)
│   ├── middlewares/      # Middleware autentikasi dan peran (RBAC)
│   ├── models/           # Kelas Model (akses data database PDO)
│   ├── providers/        # AI Providers (Nvidia NIM, Mock)
│   ├── services/         # Services (AiService, TelegramNotificationService)
│   └── views/            # Template tampilan UI (HTML/Bootstrap 5)
├── chatbot-server/       # Node.js Express server untuk Chatbot AI & Database Resolver
├── public/               # Direktori publik yang dapat diakses browser
│   ├── css/              # File style kustom
│   ├── img/              # Gambar aplikasi
│   ├── js/               # Skrip JavaScript kustom & Chatbot Widget
│   ├── .htaccess         # Aturan routing untuk public folder
│   └── index.php         # Entry point aplikasi
├── .env.example          # Template konfigurasi environment
├── .gitignore            # File pengabaian Git
├── .htaccess             # Redirect root ke public folder
└── README.md             # Dokumentasi aplikasi
```

## Cara Menjalankan Aplikasi

1. **Pastikan Web Server (Laragon / XAMPP) berjalan** (Apache dan MySQL).
2. Pindahkan atau *clone* direktori ini ke dalam folder `www` (jika Laragon) atau `htdocs` (jika XAMPP) dengan nama folder `SantriFit`.
3. Import database `santrifit_db` menggunakan file `app/database/schema.sql`.
4. Salin file `.env.example` menjadi `.env` lalu sesuaikan konfigurasi Telegram dan AI jika diperlukan.
5. Jalankan server Chatbot Node.js (jika ingin menggunakan fitur chatbot AI port 3001):
   ```bash
   cd chatbot-server
   node server.js
   ```
6. Buka *browser* dan akses URL: `http://localhost/SantriFit/public/`
