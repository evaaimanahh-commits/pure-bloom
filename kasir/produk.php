<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('kasir');


// ========================================
// PENCARIAN PRODUK
// ========================================

$cari = '';

if (isset($_GET['cari'])) {
    $cari = trim($_GET['cari']);
}


// ========================================
// AMBIL DATA PRODUK
// ========================================

if ($cari !== '') {

    $cari_aman = mysqli_real_escape_string(
        $koneksi,
        $cari
    );

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

    <title>
        Produk - Pure Bloom
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

            <div class="head">

                <div>

                    <h1>
                        Produk
                    </h1>

                    <p>
                        Daftar produk dan stok Pure Bloom.
                    </p>

                </div>

            </div>


            <!-- ========================================
                 PENCARIAN
            ========================================= -->

            <div class="card">

                <form
                    method="GET"
                    class="search"
                >

                    <input
                        type="text"
                        name="cari"
                        value="<?php echo htmlspecialchars($cari); ?>"
                        placeholder="Cari kode atau nama produk..."
                    >

                    <button
                        type="submit"
                        style="width: auto;"
                    >
                        Cari
                    </button>

                    <?php if ($cari !== '') { ?>

                        <a
                            href="produk.php"
                            class="btn soft"
                            style="width: auto;"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </form>

            </div>


            <!-- ========================================
                 TABEL PRODUK
            ========================================= -->

            <div class="table">

                <table>

                    <thead>

                        <tr>

                            <th>
                                No
                            </th>

                            <th>
                                Kode Produk
                            </th>

                            <th>
                                Nama Produk
                            </th>

                            <th>
                                Harga Jual
                            </th>

                            <th>
                                Stok
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $nomor = 1;

                    if (
                        $query_produk &&
                        mysqli_num_rows($query_produk) > 0
                    ) {

                        while (
                            $data_produk = mysqli_fetch_assoc(
                                $query_produk
                            )
                        ) {

                    ?>

                        <tr>

                            <td>
                                <?php echo $nomor++; ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $data_produk['kode_produk']
                                );
                                ?>
                            </td>

                            <td>
                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $data_produk['nama_produk']
                                    );
                                    ?>
                                </strong>
                            </td>

                            <td>

                                Rp
                                <?php
                                echo number_format(
                                    $data_produk['harga_jual'],
                                    0,
                                    ',',
                                    '.'
                                );
                                ?>

                            </td>

                            <td>
                                <?php
                                echo $data_produk['stok'];
                                ?>
                            </td>

                            <td>

                                <?php if ($data_produk['stok'] > 0) { ?>

                                    <span
                                        style="
                                            display:inline-block;
                                            padding:6px 10px;
                                            border-radius:8px;
                                            background:#e6eee9;
                                            color:#537461;
                                            font-size:12px;
                                            font-weight:700;
                                        "
                                    >
                                        Tersedia
                                    </span>

                                <?php } else { ?>

                                    <span
                                        style="
                                            display:inline-block;
                                            padding:6px 10px;
                                            border-radius:8px;
                                            background:#fae4e4;
                                            color:#a44e56;
                                            font-size:12px;
                                            font-weight:700;
                                        "
                                    >
                                        Habis
                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php

                        }

                    } else {

                    ?>

                        <tr>

                            <td
                                colspan="6"
                                style="text-align:center; padding:30px;"
                            >
                                Belum ada produk yang tersedia.

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>

</html>