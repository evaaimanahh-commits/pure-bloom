<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('kasir');

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    die('ID penjualan tidak valid.');
}


/* ========================================
   DATA TRANSAKSI
======================================== */

$query_penjualan = mysqli_prepare(
    $koneksi,
    "SELECT
        p.penjualan_id,
        p.tanggal_penjualan,
        p.total_harga,
        p.diskon,
        p.uang_bayar,
        p.kembalian,
        pl.nama_pelanggan,
        pl.nomor_telepon,
        pl.alamat,
        u.nama_lengkap AS nama_kasir
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.pelanggan_id = pl.pelanggan_id
    LEFT JOIN users u
        ON p.user_id = u.user_id
    WHERE p.penjualan_id = ?
    LIMIT 1"
);

if (!$query_penjualan) {
    die(
        'Query penjualan gagal: ' .
        mysqli_error($koneksi)
    );
}

mysqli_stmt_bind_param(
    $query_penjualan,
    'i',
    $id
);

mysqli_stmt_execute($query_penjualan);

$hasil_penjualan = mysqli_stmt_get_result(
    $query_penjualan
);

$penjualan = mysqli_fetch_assoc(
    $hasil_penjualan
);

mysqli_stmt_close($query_penjualan);


if (!$penjualan) {
    die('Data penjualan tidak ditemukan.');
}


/* ========================================
   DETAIL PRODUK
======================================== */

$query_detail = mysqli_prepare(
    $koneksi,
    "SELECT
        d.jumlah_produk,
        d.harga_satuan,
        d.subtotal,
        pr.kode_produk,
        pr.nama_produk
    FROM detail_penjualan d
    INNER JOIN produk pr
        ON d.produk_id = pr.produk_id
    WHERE d.penjualan_id = ?
    ORDER BY d.detail_id ASC"
);

if (!$query_detail) {
    die(
        'Query detail gagal: ' .
        mysqli_error($koneksi)
    );
}

mysqli_stmt_bind_param(
    $query_detail,
    'i',
    $id
);

mysqli_stmt_execute($query_detail);

$detail = mysqli_stmt_get_result(
    $query_detail
);

mysqli_stmt_close($query_detail);

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
        Detail Penjualan -
        #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .detail-box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 25px;
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .detail-header h2 {
            margin: 0 0 5px;
            color: var(--plum);
        }

        .invoice-number {
            color: var(--rose2);
            font-weight: 700;
        }

        .detail-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .info-item {
            background: var(--ivory);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 15px;
        }

        .info-item small {
            display: block;
            color: var(--muted);
            margin-bottom: 5px;
        }

        .info-item strong {
            color: var(--ink);
        }

        .detail-table {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .detail-table table {
            min-width: 650px;
        }

        .detail-summary {
            width: 360px;
            max-width: 100%;
            margin-left: auto;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid #eee;
        }

        .summary-row.discount {
            color: #63866f;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 800;
            color: var(--rose2);
            border-bottom: 0;
            padding-top: 15px;
        }

        .detail-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .detail-actions a {
            display: inline-block;
            padding: 11px 16px;
            border-radius: 10px;
            font-weight: 700;
        }

        .btn-back {
            background: var(--cream);
            color: var(--plum);
        }

        .btn-print {
            background: var(--rose2);
            color: #fff;
        }

        @media (max-width: 800px) {

            .detail-info {
                grid-template-columns: 1fr;
            }

            .detail-header {
                flex-direction: column;
            }

            .detail-summary {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main">

        <?php include '../includes/header.php'; ?>

        <div class="content">

            <div class="head">

                <div>

                    <h1>
                        Detail Penjualan
                    </h1>

                    <p>
                        Informasi lengkap transaksi.
                    </p>

                </div>

            </div>


            <div class="detail-box">

                <div class="detail-header">

                    <div>

                        <h2>
                            Pure Bloom
                        </h2>

                        <div class="invoice-number">

                            Nota #

                            <?php
                            echo str_pad(
                                $id,
                                5,
                                '0',
                                STR_PAD_LEFT
                            );
                            ?>

                        </div>

                    </div>


                    <div style="text-align:right;">

                        <small style="color:var(--muted);">
                            Tanggal transaksi
                        </small>

                        <br>

                        <strong>

                            <?php
                            echo date(
                                'd/m/Y H:i',
                                strtotime(
                                    $penjualan['tanggal_penjualan']
                                )
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <div class="detail-info">

                    <div class="info-item">

                        <small>
                            Pelanggan
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $penjualan['nama_pelanggan']
                                ?: 'Pelanggan Umum'
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="info-item">

                        <small>
                            Kasir
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $penjualan['nama_kasir']
                                ?: '-'
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="info-item">

                        <small>
                            No. Telepon
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $penjualan['nomor_telepon']
                                ?: '-'
                            );
                            ?>

                        </strong>

                    </div>

                </div>


                <div class="detail-table">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Produk</th>
                                <th>Kode</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $nomor = 1;

                        while (
                            $data =
                            mysqli_fetch_assoc($detail)
                        ):

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
                                    echo htmlspecialchars(
                                        $data['kode_produk']
                                    );
                                    ?>

                                </td>

                                <td>

                                    Rp

                                    <?php
                                    echo number_format(
                                        $data['harga_satuan'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php
                                    echo $data['jumlah_produk'];
                                    ?>

                                </td>

                                <td>

                                    <strong>

                                        Rp

                                        <?php
                                        echo number_format(
                                            $data['subtotal'],
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>

                                    </strong>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>


                <div class="detail-summary">


                    <!-- TOTAL SETELAH PRODUK -->

                    <div class="summary-row">

                        <span>
                            Total Belanja
                        </span>

                        <strong>

                            Rp

                            <?php

                            $diskon =
                                (float) (
                                    $penjualan['diskon']
                                    ?? 0
                                );

                            $total_akhir =
                                (float)
                                $penjualan['total_harga'];

                            $total_sebelum_diskon =
                                $total_akhir + $diskon;

                            echo number_format(
                                $total_sebelum_diskon,
                                0,
                                ',',
                                '.'
                            );

                            ?>

                        </strong>

                    </div>


                    <!-- DISKON -->

                    <div class="summary-row discount">

                        <span>
                            Diskon
                        </span>

                        <strong>

                            - Rp

                            <?php
                            echo number_format(
                                $diskon,
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </strong>

                    </div>


                    <!-- TOTAL AKHIR -->

                    <div class="summary-row total">

                        <span>
                            Total Transaksi
                        </span>

                        <span>

                            Rp

                            <?php
                            echo number_format(
                                $total_akhir,
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </span>

                    </div>


                    <!-- UANG BAYAR -->

                    <div class="summary-row">

                        <span>
                            Uang Bayar
                        </span>

                        <span>

                            Rp

                            <?php
                            echo number_format(
                                $penjualan['uang_bayar'],
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </span>

                    </div>


                    <!-- KEMBALIAN -->

                    <div class="summary-row">

                        <span>
                            Kembalian
                        </span>

                        <span>

                            Rp

                            <?php
                            echo number_format(
                                $penjualan['kembalian'],
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </span>

                    </div>


                </div>


                <div class="detail-actions">

                    <a
                        href="penjualan.php"
                        class="btn-back"
                    >
                        ← Kembali
                    </a>

                    <a
                        href="cetak_nota.php?id=<?php echo $id; ?>"
                        class="btn-print"
                        target="_blank"
                    >
                        🖨 Cetak Nota
                    </a>

                </div>

            </div>

        </div>

        <?php include '../includes/footer.php'; ?>

    </div>

</div>

</body>
</html>