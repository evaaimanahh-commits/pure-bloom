<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    die('ID penjualan tidak valid.');
}


/* ========================================
   AMBIL DATA TRANSAKSI
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
   AMBIL DETAIL PRODUK
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

$hasil_detail = mysqli_stmt_get_result(
    $query_detail
);

mysqli_stmt_close($query_detail);


/* ========================================
   PERHITUNGAN DISKON
======================================== */

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
        Nota #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?>
        - Pure Bloom
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            background: #f3eee9;
            font-family: Arial, sans-serif;
            color: #302a29;
        }

        .nota {
            width: 420px;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(60, 40, 40, 0.10);
        }

        .brand {
            text-align: center;
            padding-bottom: 18px;
            border-bottom: 1px dashed #cdbdb6;
        }

        .brand h1 {
            margin: 0;
            color: #9d626f;
            font-size: 30px;
            letter-spacing: .5px;
        }

        .brand p {
            margin: 4px 0;
            color: #81746f;
            font-size: 12px;
        }

        .nota-info {
            margin: 18px 0;
            font-size: 12px;
        }

        .nota-info div {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 7px;
        }

        .nota-info span:first-child {
            color: #8a7d78;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .items th {
            padding: 8px 3px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            color: #6f625e;
        }

        .items td {
            padding: 9px 3px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .summary {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px dashed #cdbdb6;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 9px;
            font-size: 13px;
        }

        .summary-row.discount {
            color: #63866f;
        }

        .summary-row.total {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            color: #9d626f;
            font-size: 18px;
            font-weight: bold;
        }

        .thank-you {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px dashed #cdbdb6;
            color: #81746f;
            font-size: 12px;
            line-height: 1.6;
        }

        .button-area {
            width: 420px;
            max-width: 100%;
            margin: 15px auto 0;
            display: flex;
            gap: 10px;
        }

        .button-area a,
        .button-area button {
            flex: 1;
            padding: 11px;
            border: none;
            border-radius: 9px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }

        .back {
            background: #e8ded8;
            color: #4d3d44;
        }

        .print {
            background: #9d626f;
            color: #ffffff;
        }

        @media print {

            body {
                padding: 0;
                background: #fff;
            }

            .nota {
                width: 100%;
                max-width: none;
                padding: 15px;
                box-shadow: none;
                border-radius: 0;
            }

            .button-area {
                display: none;
            }

        }

    </style>

</head>

<body>

<div class="nota">


    <div class="brand">

        <h1>
            Pure Bloom
        </h1>

        <p>
            BEAUTY STORE
        </p>

        <p>
            NOTA PENJUALAN
        </p>

    </div>


    <div class="nota-info">


        <div>

            <span>
                No. Nota
            </span>

            <strong>

                #<?php
                echo str_pad(
                    $id,
                    5,
                    '0',
                    STR_PAD_LEFT
                );
                ?>

            </strong>

        </div>


        <div>

            <span>
                Tanggal
            </span>

            <span>

                <?php
                echo date(
                    'd/m/Y H:i',
                    strtotime(
                        $penjualan['tanggal_penjualan']
                    )
                );
                ?>

            </span>

        </div>


        <div>

            <span>
                Pelanggan
            </span>

            <span>

                <?php
                echo htmlspecialchars(
                    $penjualan['nama_pelanggan']
                    ?: 'Pelanggan Umum'
                );
                ?>

            </span>

        </div>


        <div>

            <span>
                Kasir
            </span>

            <span>

                <?php
                echo htmlspecialchars(
                    $penjualan['nama_kasir']
                    ?: '-'
                );
                ?>

            </span>

        </div>


    </div>


    <table class="items">

        <thead>

            <tr>

                <th>
                    Produk
                </th>

                <th>
                    Qty
                </th>

                <th class="right">
                    Subtotal
                </th>

            </tr>

        </thead>


        <tbody>

        <?php while (
            $data =
            mysqli_fetch_assoc(
                $hasil_detail
            )
        ): ?>

            <tr>

                <td>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $data['nama_produk']
                        );
                        ?>

                    </strong>

                    <br>

                    <small>

                        <?php
                        echo htmlspecialchars(
                            $data['kode_produk']
                        );
                        ?>

                        · Rp

                        <?php
                        echo number_format(
                            $data['harga_satuan'],
                            0,
                            ',',
                            '.'
                        );
                        ?>

                    </small>

                </td>


                <td>

                    <?php
                    echo $data['jumlah_produk'];
                    ?>

                </td>


                <td class="right">

                    Rp

                    <?php
                    echo number_format(
                        $data['subtotal'],
                        0,
                        ',',
                        '.'
                    );
                    ?>

                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>


    <div class="summary">


        <!-- TOTAL BELANJA -->

        <div class="summary-row">

            <span>
                Total Belanja
            </span>

            <span>

                Rp

                <?php
                echo number_format(
                    $total_sebelum_diskon,
                    0,
                    ',',
                    '.'
                );
                ?>

            </span>

        </div>


        <!-- DISKON -->

        <div class="summary-row discount">

            <span>
                Diskon
            </span>

            <span>

                - Rp

                <?php
                echo number_format(
                    $diskon,
                    0,
                    ',',
                    '.'
                );
                ?>

            </span>

        </div>


        <!-- TOTAL AKHIR -->

        <div class="summary-row total">

            <span>
                Total
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


    <div class="thank-you">

        Terima kasih sudah berbelanja di
        <strong>Pure Bloom</strong> 🌸

        <br>

        Semoga harimu menyenangkan.

    </div>


</div>


<div class="button-area">

    <a
        href="penjualan.php"
        class="back"
    >
        ← Kembali
    </a>


    <button
        type="button"
        class="print"
        onclick="window.print()"
    >
        🖨 Cetak Nota
    </button>

</div>


</body>
</html>