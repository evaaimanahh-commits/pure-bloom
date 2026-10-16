<?php
session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// PROSES HAPUS PRODUK
// ========================================

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $query_hapus = mysqli_query(
        $koneksi,
        "DELETE FROM produk WHERE produk_id = $id"
    );

    header("Location: produk.php");
    exit();
}


// ========================================
// PENCARIAN PRODUK
// ========================================

$cari = '';

if (isset($_GET['cari'])) {
    $cari = trim($_GET['cari']);
}


$cari_aman = mysqli_real_escape_string(
    $koneksi,
    $cari
);


// ========================================
// AMBIL DATA PRODUK
// ========================================

if ($cari != '') {

    $query_produk = mysqli_query(
        $koneksi,
        "SELECT *
         FROM produk
         WHERE kode_produk LIKE '%$cari_aman%'
            OR nama_produk LIKE '%$cari_aman%'
         ORDER BY produk_id DESC"
    );

} else {

    $query_produk = mysqli_query(
        $koneksi,
        "SELECT *
         FROM produk
         ORDER BY produk_id DESC"
    );
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

    <title>Produk - Pure Bloom</title>

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
                 JUDUL HALAMAN
            ========================================= -->

            <div class="title">

                <div>

                    <h1>Produk</h1>

                    <p>
                        Kelola produk dan stok Pure Bloom.
                    </p>

                </div>


                <a
                    href="produk_tambah.php"
                    class="btn"
                >
                    + Tambah Produk
                </a>

            </div>


            <!-- ========================================
                 PENCARIAN
            ========================================= -->

            <div class="card">

                <form
                    method="GET"
                    style="display: flex; gap: 10px; flex-wrap: wrap;"
                >

                    <input
                        type="text"
                        name="cari"
                        value="<?php echo htmlspecialchars($cari); ?>"
                        placeholder="Cari kode atau nama produk..."
                        style="flex: 1; min-width: 220px;"
                    >

                    <button
                        type="submit"
                        class="btn"
                    >
                        Cari
                    </button>


                    <?php if ($cari != '') { ?>

                        <a
                            href="produk.php"
                            class="btn"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </form>

            </div>


            <!-- ========================================
                 TABEL PRODUK
            ========================================= -->

            <div class="card">

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Kode Produk</th>

                                <th>Nama Produk</th>

                                <th>Harga Beli</th>

                                <th>Harga Jual</th>

                                <th>Stok</th>

                                <th>Aksi</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php

                            $nomor = 1;

                            if (mysqli_num_rows($query_produk) > 0) {

                                while ($data = mysqli_fetch_assoc($query_produk)) {

                            ?>

                                <tr>

                                    <!-- Nomor -->

                                    <td>
                                        <?php echo $nomor++; ?>
                                    </td>


                                    <!-- Kode -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $data['kode_produk']
                                        );
                                        ?>

                                    </td>


                                    <!-- Nama -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $data['nama_produk']
                                        );
                                        ?>

                                    </td>


                                    <!-- Harga Beli -->

                                    <td>

                                        Rp
                                        <?php
                                        echo number_format(
                                            $data['harga_beli'],
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>

                                    </td>


                                    <!-- Harga Jual -->

                                    <td>

                                        Rp
                                        <?php
                                        echo number_format(
                                            $data['harga_jual'],
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>

                                    </td>


                                    <!-- Stok -->

                                    <td>

                                        <?php
                                        echo (int) $data['stok'];
                                        ?>

                                    </td>


                                    <!-- Aksi -->

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="produk_edit.php?id=<?php echo $data['produk_id']; ?>"
                                                class="btn btn-edit"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="produk.php?hapus=<?php echo $data['produk_id']; ?>"
                                                class="btn btn-delete"
                                                onclick="return confirm('Yakin ingin menghapus produk ini?')"
                                            >
                                                Hapus
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php

                                }

                            } else {

                            ?>

                                <tr>

                                    <td
                                        colspan="7"
                                        style="text-align: center;"
                                    >
                                        <?php
                                        if ($cari != '') {
                                            echo 'Produk yang dicari tidak ditemukan.';
                                        } else {
                                            echo 'Belum ada data produk.';
                                        }
                                        ?>
                                    </td>

                                </tr>

                            <?php

                            }

                            ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>

</html>