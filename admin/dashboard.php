<?php
session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');

// Ambil jumlah produk
$query_produk = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk");
$data_produk = mysqli_fetch_assoc($query_produk);
$total_produk = $data_produk['total'];

// Ambil jumlah pelanggan
$query_pelanggan = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pelanggan");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$total_pelanggan = $data_pelanggan['total'];

// Ambil jumlah transaksi
$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM penjualan");
$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_transaksi = $data_transaksi['total'];

// Ambil total penjualan
$query_penjualan = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(total_harga), 0) AS total FROM penjualan"
);

$data_penjualan = mysqli_fetch_assoc($query_penjualan);
$total_penjualan = $data_penjualan['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Pure Bloom</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <main class="main">

        <?php include '../includes/header.php'; ?>

        <div class="content">

            <div class="title">
                <h1>
                    Dashboard <?php echo ucfirst($_SESSION['role']); ?>
                </h1>

                <p>
                    Selamat datang di Pure Bloom Beauty Store.
                </p>
            </div>


            <div class="grid">

                <!-- Produk -->
                <div class="card">
                    <span>Produk</span>

                    <strong>
                        <?php echo $total_produk; ?>
                    </strong>
                </div>


                <!-- Pelanggan -->
                <div class="card">
                    <span>Pelanggan</span>

                    <strong>
                        <?php echo $total_pelanggan; ?>
                    </strong>
                </div>


                <!-- Transaksi -->
                <div class="card">
                    <span>Transaksi</span>

                    <strong>
                        <?php echo $total_transaksi; ?>
                    </strong>
                </div>


                <!-- Total Penjualan -->
                <div class="card">
                    <span>Total Penjualan</span>

                    <strong>
                        Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?>
                    </strong>
                </div>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>
</html>