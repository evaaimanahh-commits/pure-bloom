<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('kasir');


// ========================================
// JUMLAH PRODUK
// ========================================

$query_produk = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM produk"
);

$data_produk = mysqli_fetch_assoc($query_produk);
$total_produk = $data_produk['total'];


// ========================================
// JUMLAH PELANGGAN
// ========================================

$query_pelanggan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pelanggan"
);

$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$total_pelanggan = $data_pelanggan['total'];


// ========================================
// JUMLAH TRANSAKSI
// ========================================

$query_transaksi = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM penjualan"
);

$data_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_transaksi = $data_transaksi['total'];


// ========================================
// TOTAL PENJUALAN
// ========================================

$query_penjualan = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(total_harga), 0) AS total
     FROM penjualan"
);

$data_penjualan = mysqli_fetch_assoc($query_penjualan);
$total_penjualan = $data_penjualan['total'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Pure Bloom</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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

                <!-- PRODUK -->
                <div class="card">

                    <span>Produk</span>

                    <strong>
                        <?php echo $total_produk; ?>
                    </strong>

                </div>


                <!-- PELANGGAN -->
                <div class="card">

                    <span>Pelanggan</span>

                    <strong>
                        <?php echo $total_pelanggan; ?>
                    </strong>

                </div>


                <!-- TRANSAKSI -->
                <div class="card">

                    <span>Transaksi</span>

                    <strong>
                        <?php echo $total_transaksi; ?>
                    </strong>

                </div>


                <!-- TOTAL PENJUALAN -->
                <div class="card">

                    <span>Total Penjualan</span>

                    <strong>
                        Rp
                        <?php
                        echo number_format(
                            $total_penjualan,
                            0,
                            ',',
                            '.'
                        );
                        ?>
                    </strong>

                </div>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>

</html>