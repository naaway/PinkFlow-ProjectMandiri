PINKFLOW PROJECT MANDIRI — INSTALASI BARU

1. Cadangkan folder lama jika masih diperlukan. Proyek ini tidak menggunakan file PHP/CSS/JS lama.
2. Ekstrak folder pinkflow_projectmandiri ke C:\xampp\htdocs\
3. Jalankan Apache dan MySQL di XAMPP.
4. Buka http://localhost/phpmyadmin
5. Pilih Impor, lalu pilih database/pinkflow_projectmandiri.sql
6. Buka http://localhost/pinkflow_projectmandiri/
7. Buat admin pertama di http://localhost/pinkflow_projectmandiri/admin/buat_admin.php
8. Login di http://localhost/pinkflow_projectmandiri/admin/login.php

DATABASE: pinkflow_projectmandiri
DB USER: root
DB PASSWORD: kosong (default XAMPP). Jika berbeda, ubah config/database.php.
WHATSAPP TOKO: 6282187496556
LOKASI: Google Maps link yang diberikan pemilik proyek.

FITUR:
Homepage, katalog, pencarian, filter kategori, sorting, detail produk, keranjang,
checkout, pengurangan stok, simpan pesanan ke database, teruskan detail pesanan ke WA,
pemesanan custom, cara pesan, tentang, lokasi, admin login, dashboard, upload foto,
CRUD produk, daftar/detail pesanan, ubah status, CSS terpisah, JavaScript responsif,
animasi scroll, desain desktop dan mobile.

CATATAN:
Database baru kosong tanpa produk contoh. Tambahkan produk dan foto melalui dashboard admin.
Jangan mengimpor SQL ini ke database lama yang ingin dipertahankan.
WhatsApp dibuka dengan pesan terisi; pelanggan tetap menekan tombol Kirim.


ANIMASI WEBSITE
- GSAP: animasi pembuka header/hero, hover kartu, dan panel admin.
- Motion (Motion.dev): animasi elemen saat masuk ke area layar (scroll reveal).
- Lenis: smooth scrolling pada halaman publik dan admin.

Ketiga library dimuat melalui CDN, jadi browser membutuhkan koneksi internet saat membuka website.
Jika koneksi internet tidak tersedia, konten dan fungsi utama website tetap dapat digunakan; animasi tambahan mungkin tidak aktif.
Pengaturan prefers-reduced-motion dihormati untuk aksesibilitas.
