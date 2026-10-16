<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// VARIABEL ERROR
// ========================================

$error = '';


// ========================================
// PROSES TAMBAH PRODUK
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kode_produk = trim($_POST['kode_produk'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga_beli = $_POST['harga_beli'] ?? 0;
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $stok = $_POST['stok'] ?? 0;


    // Ubah menjadi angka
    $harga_beli = (float) $harga_beli;
    $harga_jual = (float) $harga_jual;
    $stok = (int) $stok;


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
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $query_cek,
            's',
            $kode_produk
        );

        mysqli_stmt_execute($query_cek);

        $hasil_cek = mysqli_stmt_get_result($query_cek);


        if (mysqli_num_rows($hasil_cek) > 0) {

            $error = 'Kode produk sudah digunakan.';

        } else {


            // ========================================
            // SIMPAN PRODUK
            // ========================================

            $query_simpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO produk
                (
                    kode_produk,
                    nama_produk,
                    harga_beli,
                    harga_jual,
                    stok
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )"
            );

            mysqli_stmt_bind_param(
                $query_simpan,
                'ssddi',
                $kode_produk,
                $nama_produk,
                $harga_beli,
                $harga_jual,
                $stok
            );


            if (mysqli_stmt_execute($query_simpan)) {

                header('Location: produk.php');
                exit();

            } else {

                $error = 'Produk gagal disimpan.';

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
        Tambah Produk - Pure Bloom
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
                        Tambah Produk
                    </h1>

                    <p>
                        Tambahkan produk baru ke Pure Bloom.
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
                            value="<?php echo htmlspecialchars($kode_produk ?? ''); ?>"
                            placeholder="Contoh: PB001"
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
                            value="<?php echo htmlspecialchars($nama_produk ?? ''); ?>"
                            placeholder="Contoh: Lip Tint"
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
                            value="<?php echo htmlspecialchars($harga_beli ?? ''); ?>"
                            min="0"
                            step="0.01"
                            placeholder="Contoh: 25000"
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
                            value="<?php echo htmlspecialchars($harga_jual ?? ''); ?>"
                            min="0"
                            step="0.01"
                            placeholder="Contoh: 35000"
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
                            value="<?php echo htmlspecialchars($stok ?? ''); ?>"
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
                            Simpan Produk
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