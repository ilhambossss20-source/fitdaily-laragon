FITDAILY 2.0 - JADWAL OLAHRAGA HARIAN
=====================================

CARA MENJALANKAN DI LARAGON
1. Backup folder FITDAILY lama terlebih dahulu.
2. Ekstrak folder fitdaily_laragon ke C:\laragon\www\
3. Jalankan Laragon, klik Start All.
4. Pastikan database db_olahraga sudah ada dan tabel-tabelnya sesuai struktur di bawah.
5. Buka VS Code > File > Open Folder > C:\laragon\www\fitdaily_laragon
6. Buka browser: http://localhost/fitdaily_laragon/
7. Login dengan akun yang sudah terdaftar. Jika belum punya akun, buka register.php.

STRUKTUR DATABASE YANG DIGUNAKAN
pengguna: id_pengguna, nama_pengguna, username, password, email, tanggal_daftar
kategori_olahraga: id_kategori, nama_kategori, deskripsi
olahraga: id_olahraga, id_kategori, nama_olahraga, jenis_olahraga, durasi_menit, keterangan
jadwal_olahraga: id_jadwal, id_pengguna, id_olahraga, tanggal, jam_mulai, jam_selesai, status
riwayat_olahraga: id_riwayat, id_pengguna, id_olahraga, tanggal, durasi_aktual, catatan
pengingat: id_pengingat, id_jadwal, waktu_pengingat, pesan, status

FITUR VERSI 2.0
- Dashboard: ringkasan jadwal, jadwal selesai, total durasi, pengingat aktif, grafik 7 hari, akses cepat.
- Kalender: tampilan kalender bulanan, navigasi bulan, status dan tautan edit jadwal.
- Pengingat: daftar pengingat terkait pengguna dan notifikasi browser.
- Riwayat: filter tanggal, jumlah aktivitas, total durasi, rata-rata durasi.
- Laporan: filter periode, ringkasan, ekspor CSV, cetak atau simpan sebagai PDF melalui dialog cetak browser.
- Pengaturan: ubah nama/email, ganti password, mode gelap/terang (disimpan di browser).
- Fitur lama: login, registrasi, logout, CRUD, pencarian tabel, layout responsif.

CATATAN PENTING
1. Fitur ini mengikuti struktur tabel di atas. Jika nama kolom di database berbeda, sesuaikan query sebelum digunakan.
2. Pengingat browser hanya dapat diperiksa ketika halaman Pengingat sedang terbuka dan izin notifikasi telah diberikan. Pengingat saat browser tertutup memerlukan cron/layanan penjadwalan server.
3. Ekspor PDF menggunakan fitur Print > Save as PDF pada browser.
4. Grafik dashboard memerlukan koneksi internet untuk memuat Chart.js dari CDN.
5. Mode gelap disimpan per browser/perangkat.
6. Jangan jalankan DROP DATABASE atau menghapus tabel lama.
7. Buat salinan database sebelum melakukan perubahan struktur.
8. Pastikan hanya akun pemilik yang mengakses akun pengguna tersebut; pengelolaan data lintas pengguna pada modul CRUD lama perlu disesuaikan jika aplikasi dipakai oleh banyak akun.
