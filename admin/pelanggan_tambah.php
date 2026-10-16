<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// VARIABEL
// ========================================

$error = '';

$nama_pelanggan = '';
$nomor_telepon = '';
$alamat = '';


// ========================================
// PROSES TAMBAH PELANGGAN
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');


    // ========================================
    // VALIDASI
    // ========================================

    if ($nama_pelanggan === '') {

        $error = 'Nama pelanggan wajib diisi.';

    } else {


        // ========================================
        // SIMPAN DATA PELANGGAN
        // ========================================

        $query_simpan = mysqli_prepare(
            $koneksi,
            "INSERT INTO pelanggan
            (
                nama_pelanggan,
                nomor_telepon,
                alamat
            )
            VALUES
            (
                ?,
                ?,
                ?
            )"
        );


        mysqli_stmt_bind_param(
            $query_simpan,
            'sss',
            $nama_pelanggan,
            $nomor_telepon,
            $alamat
        );


        if (mysqli_stmt_execute($query_simpan)) {

            header('Location: pelanggan.php');
            exit();

        } else {

            $error = 'Data pelanggan gagal disimpan.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Tambah Pelanggan - Pure Bloom
    </title>

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

            <!-- ========================================
                 JUDUL
            ========================================= -->

            <div class="title">

                <div>

                    <h1>
                        Tambah Pelanggan
                    </h1>

                    <p>
                        Tambahkan data pelanggan baru.
                    </p>

                </div>

                <a
                    href="pelanggan.php"
                    class="btn"
                >
                    Kembali
                </a>

            </div>


            <!-- ========================================
                 PESAN ERROR
            ========================================= -->

            <?php if ($error !== '') { ?>

                <div class="alert">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php } ?>


            <!-- ========================================
                 FORM
            ========================================= -->

            <div class="card">

                <form
                    method="POST"
                    action=""
                >

                    <!-- NAMA -->

                    <div class="form-group">

                        <label for="nama_pelanggan">
                            Nama Pelanggan
                        </label>

                        <input
                            type="text"
                            id="nama_pelanggan"
                            name="nama_pelanggan"
                            value="<?php echo htmlspecialchars($nama_pelanggan); ?>"
                            placeholder="Masukkan nama pelanggan"
                            required
                        >

                    </div>


                    <!-- NOMOR TELEPON -->

                    <div class="form-group">

                        <label for="nomor_telepon">
                            Nomor Telepon
                        </label>

                        <input
                            type="text"
                            id="nomor_telepon"
                            name="nomor_telepon"
                            value="<?php echo htmlspecialchars($nomor_telepon); ?>"
                            placeholder="Contoh: 081234567890"
                        >

                    </div>


                    <!-- ALAMAT -->

                    <div class="form-group">

                        <label for="alamat">
                            Alamat
                        </label>

                        <textarea
                            id="alamat"
                            name="alamat"
                            rows="5"
                            placeholder="Masukkan alamat pelanggan"
                        ><?php echo htmlspecialchars($alamat); ?></textarea>

                    </div>


                    <!-- TOMBOL -->

                    <div class="actions">

                        <a
                            href="pelanggan.php"
                            class="btn"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="btn"
                        >
                            Simpan Pelanggan
                        </button>

                    </div>

                </form>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>

</html>