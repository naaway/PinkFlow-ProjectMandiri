-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Okt 2026 pada 08.58
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pinkflow_mandiri`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `stock`, `image`, `description`, `created_at`) VALUES
(1, 'Buket Pink Rose', 'Buket Bunga', 150000.00, 5, 'hero-bouquet.jpg', 'Buket bunga mawar pink yang manis dan elegan untuk hadiah orang tersayang.', '2026-10-27 01:11:42'),
(2, 'Buket Thumbelina', 'Buket Bunga', 175000.00, 5, 'Buket Thumbelina.jpeg', 'Buket bunga cantik dengan nuansa manis dan elegan, cocok untuk hadiah momen spesial.', '2026-10-27 01:48:47'),
(3, 'Buket Snack', 'Buket Snack', 75000.00, 5, 'Buket Snack.jpeg', 'Buket berisi aneka snack favorit yang dikemas menarik untuk hadiah.', '2026-10-27 01:48:47'),
(4, 'Buket Uang', 'Buket Uang', 150000.00, 3, 'Buket Uang.jpeg', 'Buket hadiah dengan susunan uang yang cantik untuk momen wisuda, ulang tahun, dan perayaan spesial.', '2026-10-27 01:48:47'),
(5, 'Buket Boneka', 'Buket Bunga', 150000.00, 5, 'Buket Boneka.jpeg', 'Buket cantik dengan boneka lucu sebagai hadiah untuk orang tersayang.', '2026-10-27 01:52:44'),
(6, 'Buket Gift', 'Buket Bunga', 175000.00, 5, 'Buket Gift.jpeg', 'Buket hadiah dengan kombinasi bunga dan elemen dekoratif yang menarik.', '2026-10-27 01:52:44'),
(7, 'Buket Mawar Merah', 'Buket Bunga', 180000.00, 5, 'Buket Mawar Merah.jpeg', 'Buket mawar merah yang elegan dan romantis untuk momen spesial.', '2026-10-27 01:52:44'),
(8, 'Buket Pink & Gift', 'Buket Bunga', 200000.00, 5, 'Buket Pink & Gift.jpeg', 'Buket bernuansa pink dengan hadiah manis yang dikemas cantik.', '2026-10-27 01:52:44'),
(9, 'Round Buket', 'Buket Bunga', 175000.00, 5, 'Round Buket.PNG', 'Buket bunga berbentuk bulat dengan susunan yang rapi dan elegan.', '2026-10-27 01:52:44'),
(10, 'Snack Tower', 'Buket Snack', 200000.00, 3, 'Snack Tower.PNG', 'Menara snack dengan aneka camilan yang cocok untuk hadiah perayaan.', '2026-10-27 01:52:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
