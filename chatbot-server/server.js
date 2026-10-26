const http = require('http');
const https = require('https');
const dns = require('dns');
const fs = require('fs');
const path = require('path');

// Prefer IPv4 to avoid NAT64/IPv6 timeout issues on Windows
if (typeof dns.setDefaultResultOrder === 'function') {
    dns.setDefaultResultOrder('ipv4first');
}

// Process Error Handlers to prevent unexpected crashes
process.on('uncaughtException', (err) => {
    console.error('[Uncaught Exception]:', err.message);
});

process.on('unhandledRejection', (reason) => {
    console.error('[Unhandled Rejection]:', reason);
});

// Load environment variables from .env
const envPath = path.join(__dirname, '.env');
const env = {};
if (fs.existsSync(envPath)) {
    const lines = fs.readFileSync(envPath, 'utf-8').split('\n');
    lines.forEach(line => {
        const trimmed = line.trim();
        if (trimmed && !trimmed.startsWith('#')) {
            const idx = trimmed.indexOf('=');
            if (idx > -1) {
                const k = trimmed.substring(0, idx).trim();
                const v = trimmed.substring(idx + 1).trim();
                env[k] = v;
            }
        }
    });
}

const PORT = parseInt(process.env.PORT || env.PORT || 3001, 10);
const NOUS_API_KEY = process.env.API_KEY || env.API_KEY || 'sk-zeoF84AMgjIewHLLZsLlcLU50nXl4BrqU8dM6jUbXHZUOHxM';
const NOUS_MODEL = process.env.NOUS_MODEL || env.NOUS_MODEL || 'openai/gpt-4o-mini';

const NVIDIA_API_KEY = process.env.NVIDIA_API_KEY || env.NVIDIA_API_KEY || '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';
const NVIDIA_KEY_ID = process.env.NVIDIA_KEY_ID || env.NVIDIA_KEY_ID || '3d8b4ed4-ff3a-4039-a618-c5cab4d73c48';
const NVIDIA_MODEL = process.env.NVIDIA_MODEL || env.NVIDIA_MODEL || 'meta/llama-3.2-11b-vision-instruct';

// Fetch live database context from SantriFit internal endpoint with User Role context
function fetchDatabaseContext(user) {
    return new Promise((resolve) => {
        const userId = user?.id || '';
        const role = Array.isArray(user?.roles) ? user.roles.join(',') : (user?.roles || 'Super User');
        const url = `http://127.0.0.1/SantriFit/public/api_chatbot_db.php?user_id=${encodeURIComponent(userId)}&role=${encodeURIComponent(role)}`;

        http.get(url, { timeout: 3500 }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const json = JSON.parse(data);
                    resolve({
                        data: json.data || {},
                        summary: json.summary || {}
                    });
                } catch (e) {
                    resolve(null);
                }
            });
        }).on('error', () => {
            resolve(null);
        });
    });
}

// Check Bot Identity questions ("siapa anda", "kamu siapa", dsb)
function isBotIdentityQuestion(message) {
    const text = message.toLowerCase().trim().replace(/[?!.,]/g, '');
    const identityPatterns = [
        /^siapa (anda|kamu|dirimu|bot|asisten)$/,
        /^(anda|kamu) siapa$/,
        /^siapakah (anda|kamu)$/,
        /^kamu (ini )?siapa$/,
        /^kamu (asisten|bot|ai) apa$/,
        /^perkenalkan dirimu$/,
        /^kenalan$/
    ];
    for (const pat of identityPatterns) {
        if (pat.test(text)) return true;
    }
    return false;
}

const BOT_IDENTITY_REPLY = "Saya adalah Asisten AI resmi sistem SantriFit yang siap membantu Anda mendapatkan informasi seputar data dan layanan kesehatan di SantriFit.";

// Check User Identity questions ("siapa saya", "akun saya apa", dsb)
function isUserIdentityQuestion(message) {
    const text = message.toLowerCase().trim().replace(/[?!.,]/g, '');
    const userPatterns = [
        /^siapa (saya|aku|diriku)$/,
        /^(saya|aku) siapa$/,
        /^siapakah (saya|aku)$/,
        /^(saya|aku) ini siapa$/,
        /^siapa (saya|aku) ini$/,
        /^akun (saya|aku)( apa)?$/,
        /^siapa akun (saya|aku)$/,
        /^(saya|aku) login sebagai (apa|siapa)$/,
        /^siapa yang (sedang )?login$/,
        /^profil (saya|aku)$/,
        /^identitas (saya|aku)$/,
        /^role (saya|aku)( apa)?$/,
        /^peran (saya|aku)( apa)?$/
    ];
    for (const pat of userPatterns) {
        if (pat.test(text)) return true;
    }
    return false;
}

function getUserIdentityReply(user) {
    const username = user?.username || 'Pengguna';
    const roles = Array.isArray(user?.roles) 
        ? (user.roles.length ? user.roles.join(', ') : 'Pengguna')
        : (user?.roles || 'Pengguna');

    if (!user || !user.username || user.username === 'Pengguna') {
        return "Anda saat ini belum login ke akun SantriFit (mengakses sebagai tamu).";
    }

    return `Anda saat ini login sebagai **${username}** dengan peran (**${roles}**) di sistem SantriFit.`;
}

// DIRECT DATABASE QUERY RESOLVER (Accurate, Instant, Read-Only)
function resolveDirectDatabaseQuery(message, dbContext, user) {
    if (!dbContext || !dbContext.data) return null;
    const rawText = (message || '').trim();
    // Normalize text: lowercase, remove punctuation, collapse whitespace
    const text = rawText.toLowerCase().replace(/[?!.,;:_()\-]/g, ' ').replace(/\s+/g, ' ').trim();
    const d = dbContext.data;
    const s = dbContext.summary || {};

    const santriList = d.santri || [];
    const dokterList = d.dokter || [];
    const perawatList = d.perawat || [];
    const obatList = d.obat || [];
    const pemeriksaanList = d.pemeriksaan || [];
    const riwayatList = d.riwayat || [];
    const notifList = d.notifications || [];
    const gejalaList = d.gejala || [];
    const resepList = d.resep_obat || [];
    const rekomendasiList = d.rekomendasi || [];
    const pemberianList = d.pemberian_obat || [];
    const userList = d.users || [];

    const userRolesStr = Array.isArray(user?.roles) ? user.roles.join(', ') : (user?.roles || 'Pengguna');

    // 0. NOTIFIKASI QUERY
    if (text.includes('notifikasi') || text.includes('pemberitahuan') || text.includes('notif')) {
        const unreadList = notifList.filter(n => parseInt(n.is_read) === 0);
        const count = s.total_unread_notif !== undefined ? s.total_unread_notif : unreadList.length;

        if (count > 0) {
            let out = `🔔 **Notifikasi SantriFit untuk Anda (${userRolesStr}):**\n`;
            out += `Terdapat **${count} notifikasi** yang belum dibaca:\n\n`;
            const displayItems = (unreadList.length ? unreadList : notifList).slice(0, 4);
            displayItems.forEach((n, i) => {
                out += `${i + 1}. ${n.icon || 'ℹ️'} **${n.title}**\n   ${n.message} _(${n.time_ago || 'Baru saja'})_\n\n`;
            });
            out += `_Ketik **"Ya"** atau klik ikon lonceng **🔔** pada header chat untuk melihat panel notifikasi lengkap._`;
            return out;
        } else {
            return `🔔 Saat ini **tidak ada notifikasi baru** untuk akun Anda (${userRolesStr}). Semua pemberitahuan telah dibaca. Anda dapat menekan ikon lonceng **🔔** pada header chat untuk melihat arsip notifikasi.`;
        }
    }

    // 0.5 PENGGUNA (USER) SISTEM
    // Contoh: "pengguna siapa aja yg ada", "daftar pengguna", "berapa pengguna", "siapa saja user", "total user", "user yang ada"
    const isUserQuery = text.includes('pengguna') || (text.includes('user') && !text.includes('identity')) || text.includes('akun yang ada');
    if (isUserQuery) {
        if (/berapa.*(pengguna|user)/.test(text) || /(total|jumlah).*(pengguna|user)/.test(text) || /(pengguna|user).*ada berapa/.test(text)) {
            const total = s.total_user || userList.length;
            return `Total pengguna (user) yang terdaftar di sistem SantriFit saat ini ada **${total} pengguna**.`;
        }

        if (!userList.length) return "Data pengguna belum tersedia di database SantriFit.";
        const items = userList.map((u, i) => `${i + 1}. **${u.username}** — Peran: **${u.roles || 'Pengguna'}**`).join('\n');
        return `Berikut daftar pengguna (user) yang terdaftar di sistem SantriFit:\n${items}`;
    }

    // 1. PEMBERIAN OBAT SPESIFIK SANTRI ("aisyah diberi obat apa", "obat untuk aisyah", "aisyah minum obat apa", dsb)
    const matchSantriObat = santriList.find(st => text.includes(st.nama.toLowerCase()));
    if (matchSantriObat && (text.includes('obat') || text.includes('diberi') || text.includes('dikasih') || text.includes('resep') || text.includes('minum') || text.includes('dapat'))) {
        const santriPemberian = pemberianList.filter(p => p.nama_santri && p.nama_santri.toLowerCase().includes(matchSantriObat.nama.toLowerCase()));
        if (santriPemberian.length) {
            const items = santriPemberian.map((p, i) => `${i + 1}. **${p.nama_obat}** (Dosis: ${p.dosis || '-'}, Aturan: ${p.aturan_minum || '-'}) — Waktu: ${p.waktu_diberikan || '-'}${p.catatan ? ' (' + p.catatan + ')' : ''}`).join('\n');
            return `Santri **${matchSantriObat.nama}** tercatat telah diberikan obat keluar:\n${items}`;
        }

        const santriRekomendasi = rekomendasiList.filter(rk => rk.nama_santri && rk.nama_santri.toLowerCase().includes(matchSantriObat.nama.toLowerCase()));
        if (santriRekomendasi.length) {
            const items = santriRekomendasi.map((rk, i) => `${i + 1}. Diagnosis: **${rk.diagnosis_medis || '-'}** (Saran: ${rk.catatan_saran || '-'})`).join('\n');
            return `Santri **${matchSantriObat.nama}** belum memiliki catatan obat keluar di pemberian obat, namun terdapat rekomendasi dokter:\n${items}`;
        }

        return `Santri **${matchSantriObat.nama}** belum memiliki catatan obat yang diberikan di database SantriFit.`;
    }

    // 2. PEMBERIAN OBAT YANG KELUAR (UMUM) ("pemberian obat yang keluar", "obat yang keluar", "obat keluar", dsb)
    if (text.includes('pemberian') || text.includes('obat keluar') || text.includes('obat yang keluar') || (text.includes('obat') && (text.includes('keluar') || text.includes('diberi') || text.includes('dikasih')))) {
        if (!pemberianList.length) return "Belum ada catatan pemberian obat yang keluar di database SantriFit.";
        const items = pemberianList.map((po, i) => `${i + 1}. Santri: **${po.nama_santri}** — Obat: **${po.nama_obat}** (Dosis: ${po.dosis || '-'}, Aturan: ${po.aturan_minum || '-'}) | Waktu: ${po.waktu_diberikan || '-'}${po.catatan ? ' | ' + po.catatan : ''}`).join('\n');
        return `Berikut data pemberian obat yang keluar di SantriFit:\n${items}`;
    }

    // 3. REKOMENDASI DOKTER & DIAGNOSIS
    if (text.includes('rekomendasi') || text.includes('diagnosis') || text.includes('saran dokter')) {
        if (!rekomendasiList.length) return "Belum ada catatan rekomendasi dokter di database SantriFit.";
        const items = rekomendasiList.map((rk, i) => `${i + 1}. Santri: **${rk.nama_santri || 'Ahmad'}** — Diagnosis: **${rk.diagnosis_medis || '-'}** (Saran: ${rk.catatan_saran || '-'})`).join('\n');
        return `Berikut catatan rekomendasi dokter di SantriFit:\n${items}`;
    }

    // 4. RESEP OBAT
    if (text.includes('resep')) {
        if (!resepList.length) return "Belum ada catatan resep obat di database SantriFit.";
        const items = resepList.map((r, i) => `${i + 1}. **${r.nama_obat}** (Dosis: ${r.dosis || '-'}, Aturan: ${r.aturan_minum || '-'})`).join('\n');
        return `Berikut catatan resep obat di SantriFit:\n${items}`;
    }

    // 5. GEJALA
    if (text.includes('gejala')) {
        if (/berapa.*gejala/.test(text) || /(total|jumlah).*gejala/.test(text)) {
            return `Total jenis gejala kesehatan yang terdaftar di SantriFit saat ini ada **${s.total_gejala || gejalaList.length} jenis gejala**.`;
        }
        if (!gejalaList.length) return "Data gejala belum tersedia di database SantriFit.";
        const items = gejalaList.map((g, i) => `${i + 1}. **${g.nama_gejala}** (${g.deskripsi || '-'})`).join('\n');
        return `Berikut daftar gejala kesehatan yang tercatat di SantriFit:\n${items}`;
    }

    // 6. OBAT & STOK OBAT ("stock obat", "stok obat", "stock", "stok", "daftar obat", "obat", dsb)
    const isObatQuery = (text.includes('obat') || text.includes('stok') || text.includes('stock') || text.includes('sediaan obat'))
                        && !text.includes('keluar') && !text.includes('diberi') && !text.includes('pemberian') && !text.includes('dikasih');
    if (isObatQuery) {
        // Cek jika menanyakan spesifik nama obat tertentu (e.g. "amoxicillin", "obh", "paracetamol")
        const specificObat = obatList.find(ob => {
            const name = (ob.nama_obat || '').toLowerCase();
            const parts = name.split(/\s+/).filter(p => p.length >= 3 && !['500mg', 'mg', 'ml'].includes(p));
            return text.includes(name) || parts.some(p => text.includes(p));
        });

        // Jika hanya menanyakan obat spesifik tersebut (bukan minta semua daftar)
        if (specificObat && !text.includes('semua') && !text.includes('daftar') && !text.includes('sebutkan') && !text.includes('apa saja') && !text.includes('stock obat') && !text.includes('stok obat')) {
            return `Stok obat **${specificObat.nama_obat}** saat ini tersedia **${specificObat.stok ?? 0} ${specificObat.satuan || 'Pcs'}**.`;
        }

        // Cek jika menanyakan JUMLAH / TOTAL JENIS OBAT saja (e.g. "berapa jenis obat", "total obat", "jumlah obat")
        if (/(berapa|jumlah|total).*obat/.test(text) && !text.includes('stok') && !text.includes('stock') && !text.includes('sisa')) {
            const total = s.total_obat || obatList.length;
            return `Total jenis obat yang terdata di sistem SantriFit saat ini ada **${total} jenis obat**.`;
        }

        // Default query obat / stok obat ("stock obat", "stok obat", "daftar obat", "obat apa saja", "ada obat apa", "stok", "stock", "obat")
        if (!obatList.length) return "Data obat belum tersedia di database SantriFit.";
        const items = obatList.map((ob, i) => `${i + 1}. **${ob.nama_obat}** — Stok: **${ob.stok ?? 0} ${ob.satuan || 'Pcs'}**`).join('\n');
        return `Berikut data stok obat di SantriFit:\n${items}`;
    }

    // 5. SANTRI
    const isSantriQuery = text.includes('santri');
    if (isSantriQuery) {
        // Cek pertanyaan tentang santri spesifik (misal nama santri ada di teks)
        const matchSantri = santriList.find(st => text.includes(st.nama.toLowerCase()));
        if (matchSantri) {
            if (text.includes('pemeriksaan') || text.includes('riwayat') || text.includes('sakit') || text.includes('kesehatan') || text.includes('rekam')) {
                const pem = pemeriksaanList.filter(p => p.nama_santri && p.nama_santri.toLowerCase().includes(matchSantri.nama.toLowerCase()));
                const riw = riwayatList.filter(r => r.nama_santri && r.nama_santri.toLowerCase().includes(matchSantri.nama.toLowerCase()));

                let out = `Informasi kesehatan santri **${matchSantri.nama}** (NIS: ${matchSantri.nis}):\n`;
                if (pem.length) {
                    out += `\n**Pemeriksaan Terakhir:**\n`;
                    pem.forEach(p => {
                        out += `- Tanggal: ${p.tanggal_pemeriksaan || '-'}\n  Keluhan: ${p.keluhan_utama || '-'}\n  Suhu: ${p.suhu_tubuh || '-'}°C, TD: ${p.tekanan_darah || '-'}\n  Status: ${p.status || '-'}\n`;
                    });
                } else {
                    out += `- Belum ada catatan riwayat pemeriksaan medis.\n`;
                }

                if (riw.length) {
                    out += `\n**Riwayat Penyakit:**\n`;
                    riw.forEach(r => {
                        out += `- ${r.nama_penyakit} (Status: ${r.status || 'Aktif'})\n`;
                    });
                }
                return out;
            } else {
                return `Santri **${matchSantri.nama}** tercatat di SantriFit dengan NIS **${matchSantri.nis || '-'}**, jenis kelamin **${matchSantri.jenis_kelamin || '-'}**, dan berstatus aktif.`;
            }
        }

        // Total / Berapa Santri
        if (/berapa.*santri/.test(text) || /(total|jumlah).*santri/.test(text) || /santri.*ada berapa/.test(text) || /santri aktif/.test(text)) {
            const total = s.total_santri || santriList.length;
            return `Total santri yang tercatat di sistem SantriFit saat ini ada **${total} orang santri** (semuanya berstatus aktif).`;
        }

        // Daftar Santri
        if (text.includes('daftar') || text.includes('siapa') || text.includes('nama') || text.includes('sebutkan') || text.includes('tampilkan') || text.includes('data') || text === 'santri') {
            if (!santriList.length) return "Data santri belum tersedia di database SantriFit.";
            const items = santriList.map((st, i) => `${i + 1}. **${st.nama}** (NIS: ${st.nis || '-'}, JK: ${st.jenis_kelamin || '-'})`).join('\n');
            return `Berikut daftar santri yang terdaftar di SantriFit:\n${items}`;
        }
    }

    // 6. DOKTER
    const isDokterQuery = text.includes('dokter');
    if (isDokterQuery) {
        if (/berapa.*dokter/.test(text) || /(total|jumlah).*dokter/.test(text) || /dokter.*ada berapa/.test(text)) {
            const total = s.total_dokter || dokterList.length;
            return `Total dokter yang bertugas di SantriFit saat ini ada **${total} orang dokter**.`;
        }
        if (!dokterList.length) return "Data dokter belum tersedia di database SantriFit.";
        const items = dokterList.map((dk, i) => `${i + 1}. **${dk.nama}** (Spesialisasi: ${dk.spesialisasi || 'Umum'}, SIP: ${dk.no_izin_praktek || '-'})`).join('\n');
        return `Berikut dokter yang bertugas di SantriFit:\n${items}`;
    }

    // 7. PERAWAT
    const isPerawatQuery = text.includes('perawat');
    if (isPerawatQuery) {
        if (/berapa.*perawat/.test(text) || /(total|jumlah).*perawat/.test(text) || /perawat.*ada berapa/.test(text)) {
            const total = s.total_perawat || perawatList.length;
            return `Total perawat yang bertugas di SantriFit saat ini ada **${total} orang perawat**.`;
        }
        if (!perawatList.length) return "Data perawat belum tersedia di database SantriFit.";
        const items = perawatList.map((pr, i) => `${i + 1}. **${pr.nama}** (NIP: ${pr.nip || '-'})`).join('\n');
        return `Berikut perawat yang bertugas di SantriFit:\n${items}`;
    }

    // 8. PEMERIKSAAN KESEHATAN
    const isPemeriksaanQuery = text.includes('pemeriksaan') || text.includes('periksa') || text.includes('rekam medis');
    if (isPemeriksaanQuery) {
        if (/berapa.*pemeriksaan/.test(text) || /(total|jumlah).*pemeriksaan/.test(text)) {
            const total = s.total_pemeriksaan || pemeriksaanList.length;
            return `Total riwayat pemeriksaan kesehatan yang tercatat di database SantriFit saat ini ada **${total} pemeriksaan**.`;
        }
        if (!pemeriksaanList.length) return "Belum ada catatan riwayat pemeriksaan di database SantriFit.";
        const items = pemeriksaanList.slice(0, 5).map((p, i) => `${i + 1}. **${p.nama_santri}** (${p.tanggal_pemeriksaan || '-'}) — Keluhan: ${p.keluhan_utama || '-'} (Status: ${p.status || '-'})`).join('\n');
        return `Berikut riwayat pemeriksaan kesehatan terkini di SantriFit:\n${items}`;
    }

    // 9. GEJALA KHUSUS (Demam / Panas)
    if (text.includes('demam') || text.includes('panas')) {
        const demamList = pemeriksaanList.filter(p => 
            (p.keluhan_utama && p.keluhan_utama.toLowerCase().includes('demam')) || 
            (p.suhu_tubuh && parseFloat(p.suhu_tubuh) >= 37.5)
        );
        if (!demamList.length) {
            return "Saat ini tidak ada santri yang tercatat mengalami keluhan demam atau suhu tinggi dalam database SantriFit.";
        }
        const items = demamList.map(p => `- **${p.nama_santri}** (Suhu: ${p.suhu_tubuh || '-'}°C, Keluhan: ${p.keluhan_utama || '-'})`).join('\n');
        return `Santri yang tercatat mengalami demam atau suhu tubuh tinggi:\n${items}`;
    }

    // 10. SUMMARY KHUSUS (HANYA jika user secara eksplisit meminta rangkuman/rekap seluruh sistem)
    if (text.includes('ringkasan') || text.includes('rekap') || text.includes('rangkuman') || text.includes('semua data') || text.includes('total data') || text.includes('statistik database')) {
        return `Informasi ringkasan database SantriFit: Total santri ${s.total_santri || 0}, dokter ${s.total_dokter || 0}, perawat ${s.total_perawat || 0}, obat ${s.total_obat || 0}, gejala ${s.total_gejala || 0}, dan pemeriksaan tercatat ${s.total_pemeriksaan || 0}.`;
    }


    return null;
}

// INTELLIGENT TOPIC-AWARE FALLBACK (Never dumps unrelated system metrics!)
function getTopicAwareFallback(message, dbContext, user) {
    const direct = resolveDirectDatabaseQuery(message, dbContext, user);
    if (direct) return direct;

    const rawText = (message || '').toLowerCase();
    const d = dbContext?.data || {};
    const s = dbContext?.summary || {};
    const obatList = d.obat || [];
    const santriList = d.santri || [];
    const dokterList = d.dokter || [];
    const perawatList = d.perawat || [];
    const pemeriksaanList = d.pemeriksaan || [];

    if (rawText.includes('obat') || rawText.includes('stok') || rawText.includes('stock')) {
        if (!obatList.length) return "Data obat belum tersedia di database SantriFit.";
        const items = obatList.map((ob, i) => `${i + 1}. **${ob.nama_obat}** — Stok: **${ob.stok ?? 0} ${ob.satuan || 'Pcs'}**`).join('\n');
        return `Berikut data stok obat di SantriFit:\n${items}`;
    }

    if (rawText.includes('santri')) {
        if (!santriList.length) return "Data santri belum tersedia di database SantriFit.";
        const items = santriList.map((st, i) => `${i + 1}. **${st.nama}** (NIS: ${st.nis || '-'})`).join('\n');
        return `Berikut daftar santri di SantriFit:\n${items}`;
    }

    if (rawText.includes('dokter')) {
        if (!dokterList.length) return "Data dokter belum tersedia di database SantriFit.";
        const items = dokterList.map((dk, i) => `${i + 1}. **${dk.nama}** (Spesialisasi: ${dk.spesialisasi || 'Umum'})`).join('\n');
        return `Berikut dokter yang bertugas di SantriFit:\n${items}`;
    }

    if (rawText.includes('perawat')) {
        if (!perawatList.length) return "Data perawat belum tersedia di database SantriFit.";
        const items = perawatList.map((pr, i) => `${i + 1}. **${pr.nama}** (NIP: ${pr.nip || '-'})`).join('\n');
        return `Berikut perawat yang bertugas di SantriFit:\n${items}`;
    }

    if (rawText.includes('pemeriksaan') || rawText.includes('periksa') || rawText.includes('keluhan')) {
        if (!pemeriksaanList.length) return "Belum ada riwayat pemeriksaan kesehatan di database SantriFit.";
        const items = pemeriksaanList.slice(0, 5).map((p, i) => `${i + 1}. **${p.nama_santri}** (${p.tanggal_pemeriksaan || '-'}) - Keluhan: ${p.keluhan_utama || '-'}`).join('\n');
        return `Berikut riwayat pemeriksaan kesehatan di SantriFit:\n${items}`;
    }

    if (rawText.includes('ringkasan') || rawText.includes('rekap') || rawText.includes('rangkuman') || rawText.includes('semua data') || rawText.includes('statistik')) {
        return `Informasi dari database SantriFit: Total santri ${s.total_santri || 0}, dokter ${s.total_dokter || 0}, perawat ${s.total_perawat || 0}, obat ${s.total_obat || 0}, dan pemeriksaan tercatat ${s.total_pemeriksaan || 0}.`;
    }

    return "Data tersebut tidak ditemukan dalam database SantriFit.";
}


// Build System Prompt with DB Context and Active User Session
function buildSystemPrompt(dbContext, user) {
    const username = user?.username || 'Pengguna';
    const roles = Array.isArray(user?.roles)
        ? (user.roles.length ? user.roles.join(', ') : 'Pengguna')
        : (user?.roles || 'Pengguna');

    let dbString = 'Data database belum dapat diakses.';
    if (dbContext && dbContext.data) {
        const d = dbContext.data;
        const s = dbContext.summary || {};
        dbString = `
DATA TERKINI DATABASE SANFTRIFIT:
- Total Santri: ${s.total_santri || (d.santri ? d.santri.length : 0)} (Semua Aktif)
- Daftar Santri: ${JSON.stringify(d.santri || [])}
- Total Dokter: ${s.total_dokter || (d.dokter ? d.dokter.length : 0)}
- Daftar Dokter: ${JSON.stringify(d.dokter || [])}
- Total Perawat: ${s.total_perawat || (d.perawat ? d.perawat.length : 0)}
- Daftar Perawat: ${JSON.stringify(d.perawat || [])}
- Total Obat: ${s.total_obat || (d.obat ? d.obat.length : 0)}
- Daftar Obat: ${JSON.stringify(d.obat || [])}
- Riwayat Pemeriksaan: ${JSON.stringify(d.pemeriksaan || [])}
- Riwayat Penyakit: ${JSON.stringify(d.riwayat || [])}
- Rekomendasi Dokter: ${JSON.stringify(d.rekomendasi || [])}
- Resep Obat: ${JSON.stringify(d.resep_obat || [])}
- Pemberian Obat: ${JSON.stringify(d.pemberian_obat || [])}
- Master Gejala: ${JSON.stringify(d.gejala || [])}
- Total Pengguna (User): ${s.total_user || (d.users ? d.users.length : 0)}
- Daftar Pengguna (User): ${JSON.stringify(d.users || [])}
- Notifikasi Aktif: ${JSON.stringify(d.notifications || [])}
`;
    }

    return `Kamu adalah chatbot AI resmi sistem SantriFit (Sistem Manajemen Kesehatan Santri).

INFORMASI PENGGUNA YANG LOGIN:
- Username: ${username}
- Peran (Role): ${roles}

ATURAN UTAMA:
1. JAWAB HANYA SESUAI YANG DITANYAKAN (STRICT / SANGAT PENTING):
   - Jawab HANYA topik yang ditanyakan user secara spesifik, to-the-point, dan akurat!
   - Jika ditanya tentang PENGGUNA / USER SISTEM (contoh: "pengguna siapa aja yg ada", "daftar user", "total user"), jawab HANYA daftar pengguna dan perannya dari data Pengguna di atas!
   - Jika ditanya tentang OBAT atau STOK OBAT, jawab HANYA tentang obat dan stoknya. JANGAN PERNAH menyertakan informasi santri, dokter, perawat, atau pemeriksaan!
   - Jika ditanya tentang PEMBERIAN OBAT / OBAT KELUAR / OBAT YANG DIBERIKAN KEPADA SANTRI (contoh: "aisyah diberi obat apa", "obat keluar"), jawab riwayat obat yang telah keluar/diberikan kepada santri tersebut dari data Pemberian Obat!
   - Jika ditanya tentang SANTRI, jawab HANYA tentang santri. JANGAN sebutkan obat atau dokter!
   - Jika ditanya tentang DOKTER, jawab HANYA tentang dokter.
   - Jika ditanya tentang PERAWAT, jawab HANYA tentang perawat.
   - Jika ditanya tentang PEMERIKSAAN, jawab HANYA tentang pemeriksaan.
2. Jawab pertanyaan berdasarkan DATA TERKINI DATABASE SANFTRIFIT di atas.
3. Jika ditanya jumlah santri (contoh: "ada berapa santri", "total santri"), jawab langsung jumlahnya: "Total santri yang tercatat di database SantriFit saat ini ada [X] orang santri."
4. Jika ditanya siapa saya / akun saya: "Anda saat ini login sebagai **${username}** dengan peran (**${roles}**) di sistem SantriFit."
5. Jika ditanya siapa anda: "Saya adalah Asisten AI resmi sistem SantriFit yang siap membantu Anda mendapatkan informasi seputar data dan layanan kesehatan di SantriFit."
6. Jawaban harus singkat, padat, sopan, dan jelas (maksimal 2–4 kalimat kecuali diminta daftar).
7. Untuk pertanyaan medis: Bersifat informatif dan edukasi saja, bukan pengganti pemeriksaan dokter.
8. Jika data benar-benar tidak ada di database SantriFit: Jawab: "Data tersebut tidak ditemukan dalam database SantriFit."
9. Jika pertanyaan sama sekali di luar SantriFit (coding, cuaca, politik): "Maaf, saya hanya dapat membantu informasi yang berkaitan dengan SantriFit."

${dbString}
`;
}

// Check if request is completely unrelated to SantriFit / coding
function isUnrelatedTopic(message) {
    const text = message.toLowerCase().trim();
    
    // Explicit coding / technical generation
    const codingPatterns = [
        /\bcoding\b/, /\bkoding\b/, /\bpython\b/, /\bjavascript\b/, /\bphp\b/,
        /\bhtml\b/, /\bcss\b/, /\bjava\b/, /\bc\+\+\b/, /\bc#\b/,
        /\bscript\b/, /\bprogramming\b/, /\bdef\s+\w+/,
        /\balgoritma\b/, /\bbuatkan\s+koding/i, /\bbuatkan\s+coding/i,
        /\bbuatkan\s+skrip/i, /\bbuatkan\s+program/i, /\btuliskan\s+kode/i,
        /\bbuatkan\s+kode/i, /\bsource\s*code\b/
    ];

    for (const pat of codingPatterns) {
        if (pat.test(text)) {
            return true;
        }
    }

    // Weather, external politics, games
    const externalPatterns = [
        /\bcuaca\b/, /\bramalan\s+cuaca\b/, /\bgempa\b/, /\bpresiden\s+amerika\b/,
        /\bgame\b/, /\bsepak\s*bola\b/, /\bklasemen\b/
    ];

    for (const pat of externalPatterns) {
        if (pat.test(text)) {
            return true;
        }
    }

    return false;
}

const UNRELATED_REPLY = "Maaf, saya hanya dapat membantu informasi yang berkaitan dengan SantriFit.";

// Call Nvidia NIM with active model (meta/llama-3.2-11b-vision-instruct)
function callNvidiaNIM(messages, systemPrompt, model = NVIDIA_MODEL) {
    return new Promise((resolve, reject) => {
        const payload = JSON.stringify({
            model: model,
            messages: [
                { role: 'system', content: systemPrompt },
                ...messages
            ],
            temperature: 0.2,
            max_tokens: 500
        });

        const req = https.request('https://integrate.api.nvidia.com/v1/chat/completions', {
            method: 'POST',
            family: 4,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${NVIDIA_API_KEY}`,
                'Content-Length': Buffer.byteLength(payload)
            },
            timeout: 10000
        }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                if (res.statusCode >= 200 && res.statusCode < 300) {
                    try {
                        const json = JSON.parse(data);
                        const choice = json.choices?.[0];
                        const reply = choice?.message?.content || choice?.message?.reasoning_content || 'Data tersebut tidak ditemukan dalam database SantriFit.';
                        resolve({ reply, model, provider: 'NVIDIA NIM (SantriFit Engine)' });
                    } catch (e) {
                        reject(new Error('Invalid JSON dari Nvidia NIM: ' + data));
                    }
                } else {
                    reject(new Error(`Nvidia NIM HTTP ${res.statusCode}: ${data}`));
                }
            });
        });

        req.on('error', (err) => reject(new Error('Koneksi ke Nvidia NIM gagal: ' + err.message)));
        req.on('timeout', () => {
            req.destroy();
            reject(new Error('Nvidia NIM timeout.'));
        });

        req.write(payload);
        req.end();
    });
}

const server = http.createServer(async (req, res) => {
    // CORS & Private Network headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    res.setHeader('Access-Control-Allow-Private-Network', 'true');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const host = req.headers.host || `127.0.0.1:${PORT}`;
    const parsedUrl = new URL(req.url, `http://${host}`);

    // Health Check
    if (req.method === 'GET' && (parsedUrl.pathname === '/' || parsedUrl.pathname === '/health' || parsedUrl.pathname === '/api/status')) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: 'online',
            service: 'SantriFit Database AI Chatbot Server',
            model: NVIDIA_MODEL,
            provider: 'SantriFit AI Engine (NVIDIA NIM)',
            timestamp: new Date().toISOString()
        }));
        return;
    }

    // Chat Endpoint
    if (req.method === 'POST' && parsedUrl.pathname === '/api/chat') {
        let body = '';
        req.on('data', chunk => body += chunk.toString());
        req.on('end', async () => {
            try {
                const parsed = JSON.parse(body || '{}');
                const userMessage = parsed.message?.trim();
                const conversationHistory = Array.isArray(parsed.history) ? parsed.history : [];
                const user = parsed.user || {};

                if (!userMessage) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: false, error: 'Pesan wajib diisi.' }));
                    return;
                }

                // 1. Check User Identity ("Siapa saya", "Siapa aku", "Akun saya apa")
                if (isUserIdentityQuestion(userMessage)) {
                    const reply = getUserIdentityReply(user);
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        success: true,
                        reply: reply,
                        model: 'SantriFit-User-Identity',
                        provider: 'SantriFit Official',
                        timestamp: new Date().toISOString()
                    }));
                    return;
                }

                // 2. Check Bot Identity ("Siapa anda", "Kamu siapa")
                if (isBotIdentityQuestion(userMessage)) {
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        success: true,
                        reply: BOT_IDENTITY_REPLY,
                        model: 'SantriFit-Bot-Identity',
                        provider: 'SantriFit Official',
                        timestamp: new Date().toISOString()
                    }));
                    return;
                }

                // 3. Check Unrelated Topics (Coding, weather, etc.)
                if (isUnrelatedTopic(userMessage)) {
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        success: true,
                        reply: UNRELATED_REPLY,
                        model: 'SantriFit-Official-Policy',
                        provider: 'SantriFit Chatbot',
                        timestamp: new Date().toISOString()
                    }));
                    return;
                }

                // 4. Fetch live database context (with User Role context)
                const dbContext = await fetchDatabaseContext(user);

                // 5. DIRECT DATABASE & NOTIFICATION QUERY MATCHING (Instant & 100% Accurate)
                const directAnswer = resolveDirectDatabaseQuery(userMessage, dbContext, user);
                if (directAnswer) {
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        success: true,
                        reply: directAnswer,
                        model: 'SantriFit-Live-Database',
                        provider: 'Database SantriFit',
                        timestamp: new Date().toISOString()
                    }));
                    return;
                }

                // 6. Natural Language / AI Query via fast Llama 3.2 Vision Instruct
                const systemPrompt = buildSystemPrompt(dbContext, user);
                const messages = [];
                const recent = conversationHistory.slice(-4);
                recent.forEach(h => {
                    if (h.role && h.content) {
                        messages.push({
                            role: h.role === 'user' ? 'user' : 'assistant',
                            content: String(h.content)
                        });
                    }
                });
                messages.push({ role: 'user', content: userMessage });

                let result;
                try {
                    result = await callNvidiaNIM(messages, systemPrompt, NVIDIA_MODEL);
                } catch (aiErr) {
                    console.warn(`[AI Engine Notice]: ${aiErr.message}. Utilizing Topic-Aware Fallback.`);
                    const fallbackReply = getTopicAwareFallback(userMessage, dbContext, user);
                    result = {
                        reply: fallbackReply,
                        model: 'SantriFit-Live-Database',
                        provider: 'Database SantriFit'
                    };
                }

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    reply: result.reply,
                    model: result.model,
                    provider: result.provider,
                    timestamp: new Date().toISOString()
                }));
            } catch (err) {
                console.error('[Chatbot Error]:', err.message);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    reply: 'Data tersebut tidak ditemukan dalam database SantriFit.',
                    model: 'SantriFit-Guardrail',
                    provider: 'SantriFit Official'
                }));
            }
        });
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Endpoint tidak ditemukan.' }));
});

server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
        console.error(`[Port In Use]: Port ${PORT} sedang dipakai proses lain.`);
    } else {
        console.error('[Server Error]:', err.message);
    }
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`=================================================`);
    console.log(`🚀 SantriFit Database Chatbot is RUNNING on Port ${PORT}!`);
    console.log(`📊 Connected to SantriFit Live Database Context`);
    console.log(`🔔 Notification System Integrated`);
    console.log(`🧠 Fast AI Engine: ${NVIDIA_MODEL}`);
    console.log(`👤 User Identity & Account Recognition Active`);
    console.log(`🤖 Bot Identity & Polite Refusal Rules Enforced`);
    console.log(`=================================================`);
});