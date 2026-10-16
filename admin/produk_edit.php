<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// CEK ID PRODUK
// ========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: produk.php');
    exit();
}

$produk_id = (int) $_GET['id'];


// ========================================
// AMBIL DATA PRODUK
// ========================================

$query_produk = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM produk
     WHERE produk_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $query_produk,
    'i',
    $produk_id
);

mysqli_stmt_execute($query_produk);

$hasil_produk = mysqli_stmt_get_result($query_produk);

$data_produk = mysqli_fetch_assoc($hasil_produk);


if (!$data_produk) {
    header('Location: produk.php');
    exit();
}


// ========================================
// VARIABEL FORM
// ========================================

$kode_produk = $data_produk['kode_produk'];
$nama_produk = $data_produk['nama_produk'];
$harga_beli = $data_produk['harga_beli'];
$harga_jual = $data_produk['harga_jual'];
$stok = $data_produk['stok'];

$error = '';


// ========================================
// PROSES UPDATE
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kode_produk = trim($_POST['kode_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_beli = (float) ($_POST['harga_beli'] ?? 0);
    $harga_jual = (float) ($_POST['harga_jual'] ?? 0);
    $stok = (int) ($_POST['stok'] ?? 0);


    // ========================================
    // VALIDASI
    // ========================================

    if ($kode_produk === '') {

        $error = 'Kode produk wajib diisi.';

    } elseif ($nama_produk === '') {

        $error = 'Nama produk wajib diisi.';

    } elseif ($harga_beli < 0) {

        $error = 'Harga beli tidak boleh kurang dari 0.';

    } elseif ($harga_jual < 0) {

        $error = 'Harga jual tidak boleh kurang dari 0.';

    } elseif ($stok < 0) {

        $error = 'Stok tidak boleh kurang dari 0.';

    } else {


        // ========================================
        // CEK KODE PRODUK
        // ========================================

        $query_cek = mysqli_prepare(
            $koneksi,
            "SELECT produk_id
             FROM produk
             WHERE kode_produk = ?
             AND produk_id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $query_cek,
            'si',
            $kode_produk,
            $produk_id
        );

        mysqli_stmt_execute($query_cek);

        $hasil_cek = mysqli_stmt_get_result($query_cek);


        if (mysqli_num_rows($hasil_cek) > 0) {

            $error = 'Kode produk sudah digunakan oleh produk lain.';

        } else {


            // ========================================
            // UPDATE PRODUK
            // ========================================

            $query_update = mysqli_prepare(
                $koneksi,
                "UPDATE produk
                 SET
                    kode_produk = ?,
                    nama_produk = ?,
                    harga_beli = ?,
                    harga_jual = ?,
                    stok = ?
                 WHERE produk_id = ?"
            );

            mysqli_stmt_bind_param(
                $query_update,
                'ssddii',
                $kode_produk,
                $nama_produk,
                $harga_beli,
                $harga_jual,
                $stok,
                $produk_id
            );


            if (mysqli_stmt_execute($query_update)) {

                header('Location: produk.php');
                exit();

            } else {

                $error = 'Produk gagal diperbarui.';

            }
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
        Edit Produk - Pure Bloom
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

            <div class="title">

                <div>

                    <h1>
                        Edit Produk
                    </h1>

                    <p>
                        Perbarui data produk Pure Bloom.
                    </p>

                </div>

                <a
                    href="produk.php"
                    class="btn"
                >
                    Kembali
                </a>

            </div>


            <?php if ($error !== '') { ?>

                <div class="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php } ?>


            <div class="card">

                <form
                    method="POST"
                    action=""
                >

                    <!-- KODE PRODUK -->

                    <div class="form-group">

                        <label for="kode_produk">
                            Kode Produk
                        </label>

                        <input
                            type="text"
                            id="kode_produk"
                            name="kode_produk"
                            value="<?php echo htmlspecialchars($kode_produk); ?>"
                            required
                        >

                    </div>


                    <!-- NAMA PRODUK -->

                    <div class="form-group">

                        <label for="nama_produk">
                            Nama Produk
                        </label>

                        <input
                            type="text"
                            id="nama_produk"
                            name="nama_produk"
                            value="<?php echo htmlspecialchars($nama_produk); ?>"
                            required
                        >

                    </div>


                    <!-- HARGA BELI -->

                    <div class="form-group">

                        <label for="harga_beli">
                            Harga Beli
                        </label>

                        <input
                            type="number"
                            id="harga_beli"
                            name="harga_beli"
                            value="<?php echo htmlspecialchars($harga_beli); ?>"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>


                    <!-- HARGA JUAL -->

                    <div class="form-group">

                        <label for="harga_jual">
                            Harga Jual
                        </label>

                        <input
                            type="number"
                            id="harga_jual"
                            name="harga_jual"
                            value="<?php echo htmlspecialchars($harga_jual); ?>"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>


                    <!-- STOK -->

                    <div class="form-group">

                        <label for="stok">
                            Stok
                        </label>

                        <input
                            type="number"
                            id="stok"
                            name="stok"
                            value="<?php echo htmlspecialchars($stok); ?>"
                            min="0"
                            required
                        >

                    </div>


                    <!-- TOMBOL -->

                    <div class="actions">

                        <a
                            href="produk.php"
                            class="btn"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="btn"
                        >
                            Simpan Perubahan
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