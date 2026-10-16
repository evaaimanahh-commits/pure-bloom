<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


/*
 * BULAN DAN TAHUN
 */

$bulan =
    isset($_GET['bulan'])
    ? (int) $_GET['bulan']
    : (int) date('m');

$tahun =
    isset($_GET['tahun'])
    ? (int) $_GET['tahun']
    : (int) date('Y');


if (
    $bulan < 1 ||
    $bulan > 12
) {

    $bulan =
        (int) date('m');

}


if (
    $tahun < 2000 ||
    $tahun > 2100
) {

    $tahun =
        (int) date('Y');

}


/*
 * TOTAL TRANSAKSI
 */

$query_jumlah =
    mysqli_prepare(
        $koneksi,
        "SELECT
            COUNT(*) AS jumlah_transaksi,
            COALESCE(SUM(total_harga), 0) AS total_penjualan,
            COALESCE(SUM(diskon), 0) AS total_diskon,
            COALESCE(SUM(uang_bayar), 0) AS total_uang
        FROM penjualan
        WHERE MONTH(tanggal_penjualan) = ?
        AND YEAR(tanggal_penjualan) = ?"
    );


mysqli_stmt_bind_param(
    $query_jumlah,
    'ii',
    $bulan,
    $tahun
);


mysqli_stmt_execute(
    $query_jumlah
);


$hasil_jumlah =
    mysqli_stmt_get_result(
        $query_jumlah
    );


$ringkasan =
    mysqli_fetch_assoc(
        $hasil_jumlah
    );


mysqli_stmt_close(
    $query_jumlah
);


/*
 * REKAP PER HARI
 */

$query_harian =
    mysqli_prepare(
        $koneksi,
        "SELECT
            DATE(tanggal_penjualan) AS tanggal,
            COUNT(*) AS jumlah_transaksi,
            COALESCE(SUM(total_harga), 0) AS total_penjualan
        FROM penjualan
        WHERE MONTH(tanggal_penjualan) = ?
        AND YEAR(tanggal_penjualan) = ?
        GROUP BY DATE(tanggal_penjualan)
        ORDER BY tanggal ASC"
    );


mysqli_stmt_bind_param(
    $query_harian,
    'ii',
    $bulan,
    $tahun
);


mysqli_stmt_execute(
    $query_harian
);


$hasil_harian =
    mysqli_stmt_get_result(
        $query_harian
    );


mysqli_stmt_close(
    $query_harian
);


/*
 * PRODUK TERLARIS
 */

$query_produk =
    mysqli_prepare(
        $koneksi,
        "SELECT
            pr.nama_produk,
            SUM(d.jumlah_produk) AS jumlah_terjual,
            SUM(d.subtotal) AS total_penjualan
        FROM detail_penjualan d
        INNER JOIN produk pr
            ON d.produk_id = pr.produk_id
        INNER JOIN penjualan p
            ON d.penjualan_id = p.penjualan_id
        WHERE MONTH(p.tanggal_penjualan) = ?
        AND YEAR(p.tanggal_penjualan) = ?
        GROUP BY d.produk_id
        ORDER BY jumlah_terjual DESC
        LIMIT 10"
    );


mysqli_stmt_bind_param(
    $query_produk,
    'ii',
    $bulan,
    $tahun
);


mysqli_stmt_execute(
    $query_produk
);


$hasil_produk =
    mysqli_stmt_get_result(
        $query_produk
    );


mysqli_stmt_close(
    $query_produk
);


/*
 * NAMA BULAN
 */

$nama_bulan = [

    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'

];

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
        Laporan Penjualan - Pure Bloom
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .filter-laporan {

            background:#fff;
            border:1px solid var(--border);
            border-radius:18px;
            padding:20px;
            margin-bottom:20px;

        }

        .filter-laporan form {

            display:grid;
            grid-template-columns:1fr 1fr auto;
            gap:10px;
            align-items:end;

        }

        .laporan-card {

            margin-bottom:20px;

        }

        .laporan-title {

            margin-bottom:15px;

        }

        .report-print {

            width:auto;

        }

        @media(max-width:600px) {

            .filter-laporan form {

                grid-template-columns:1fr;

            }

            .report-print {

                width:100%;

            }

        }

        @media print {

            aside,
            header,
            footer,
            .filter-laporan,
            .no-print {

                display:none !important;

            }

            .content {

                padding:0;

            }

            body {

                background:#fff;

            }

        }

    </style>

</head>

<body>

<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <main class="main">

        <?php include '../includes/header.php'; ?>

        <div class="content">


            <div class="head">

                <div>

                    <h1>
                        Laporan Penjualan
                    </h1>

                    <p>
                        Rekap hasil penjualan berdasarkan periode.
                    </p>

                </div>

                <button
                    type="button"
                    class="report-print no-print"
                    onclick="window.print()"
                >
                    🖨 Cetak Laporan
                </button>

            </div>


            <!-- FILTER -->

            <div class="filter-laporan no-print">

                <form method="GET">

                    <div>

                        <label>
                            Bulan
                        </label>

                        <select name="bulan">

                            <?php foreach (
                                $nama_bulan
                                as $nomor_bulan => $nama
                            ) { ?>

                                <option
                                    value="<?php echo $nomor_bulan; ?>"
                                    <?php

                                    if (
                                        $bulan ==
                                        $nomor_bulan
                                    ) {
                                        echo 'selected';
                                    }

                                    ?>
                                >

                                    <?php echo $nama; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <div>

                        <label>
                            Tahun
                        </label>

                        <select name="tahun">

                            <?php

                            for (
                                $y = date('Y') - 5;
                                $y <= date('Y') + 1;
                                $y++
                            ) {

                            ?>

                                <option
                                    value="<?php echo $y; ?>"
                                    <?php

                                    if (
                                        $tahun == $y
                                    ) {
                                        echo 'selected';
                                    }

                                    ?>
                                >

                                    <?php echo $y; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <button type="submit">

                        Tampilkan

                    </button>

                </form>

            </div>


            <!-- JUDUL LAPORAN -->

            <div class="card laporan-card">

                <h2 class="laporan-title">

                    Laporan Penjualan
                    <?php echo $nama_bulan[$bulan]; ?>
                    <?php echo $tahun; ?>

                </h2>


                <div class="grid">

                    <div class="card">

                        <span>
                            Jumlah Transaksi
                        </span>

                        <strong>

                            <?php

                            echo number_format(
                                $ringkasan['jumlah_transaksi'],
                                0,
                                ',',
                                '.'
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="card">

                        <span>
                            Total Penjualan
                        </span>

                        <strong>

                            Rp

                            <?php

                            echo number_format(
                                $ringkasan['total_penjualan'],
                                0,
                                ',',
                                '.'
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="card">

                        <span>
                            Total Diskon
                        </span>

                        <strong>

                            Rp

                            <?php

                            echo number_format(
                                $ringkasan['total_diskon'],
                                0,
                                ',',
                                '.'
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="card">

                        <span>
                            Total Pembayaran
                        </span>

                        <strong>

                            Rp

                            <?php

                            echo number_format(
                                $ringkasan['total_uang'],
                                0,
                                ',',
                                '.'
                            );

                            ?>

                        </strong>

                    </div>

                </div>

            </div>


            <!-- REKAP HARIAN -->

            <div class="card laporan-card">

                <h2>
                    Rekap Penjualan Per Hari
                </h2>

                <div class="table">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Tanggal</th>

                                <th>Jumlah Transaksi</th>

                                <th>Total Penjualan</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $nomor = 1;

                        while (
                            $data =
                            mysqli_fetch_assoc(
                                $hasil_harian
                            )
                        ) {

                        ?>

                            <tr>

                                <td>
                                    <?php echo $nomor++; ?>
                                </td>

                                <td>

                                    <?php

                                    echo date(
                                        'd/m/Y',
                                        strtotime(
                                            $data['tanggal']
                                        )
                                    );

                                    ?>

                                </td>

                                <td>

                                    <?php

                                    echo number_format(
                                        $data['jumlah_transaksi'],
                                        0,
                                        ',',
                                        '.'
                                    );

                                    ?>

                                    transaksi

                                </td>

                                <td>

                                    Rp

                                    <?php

                                    echo number_format(
                                        $data['total_penjualan'],
                                        0,
                                        ',',
                                        '.'
                                    );

                                    ?>

                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- PRODUK TERLARIS -->

            <div class="card laporan-card">

                <h2>
                    Produk Terlaris
                </h2>

                <div class="table">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Produk</th>

                                <th>Jumlah Terjual</th>

                                <th>Total Penjualan</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $nomor =
                            1;

                        while (
                            $data =
                            mysqli_fetch_assoc(
                                $hasil_produk
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
                                        $data['nama_produk']
                                    );

                                    ?>

                                </td>

                                <td>

                                    <?php

                                    echo number_format(
                                        $data['jumlah_terjual'],
                                        0,
                                        ',',
                                        '.'
                                    );

                                    ?>

                                    item

                                </td>

                                <td>

                                    Rp

                                    <?php

                                    echo number_format(
                                        $data['total_penjualan'],
                                        0,
                                        ',',
                                        '.'
                                    );

                                    ?>

                                </td>

                            </tr>

                        <?php } ?>

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