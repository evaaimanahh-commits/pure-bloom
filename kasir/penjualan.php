<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('kasir');

$query = mysqli_query(
    $koneksi,
    "SELECT
        p.penjualan_id,
        p.tanggal_penjualan,
        p.total_harga,
        p.uang_bayar,
        p.kembalian,
        pl.nama_pelanggan,
        u.nama_lengkap AS nama_kasir
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.pelanggan_id = pl.pelanggan_id
    LEFT JOIN users u
        ON p.user_id = u.user_id
    ORDER BY p.penjualan_id DESC"
);

if (!$query) {
    die(
        'Gagal mengambil data penjualan: ' .
        mysqli_error($koneksi)
    );
}

$berhasil = isset($_GET['berhasil']);
$id_berhasil = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Penjualan - Pure Bloom</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main">

        <?php include '../includes/header.php'; ?>

        <div class="content">

            <div class="head">

                <div>
                    <h1>Riwayat Penjualan</h1>

                    <p>
                        Daftar transaksi yang telah dilakukan.
                    </p>
                </div>

                <a
                    href="transaksi.php"
                    class="btn"
                >
                    + Transaksi Baru
                </a>

            </div>

            <?php if ($berhasil): ?>

                <div
                    class="alert"
                    style="
                        background:#e8f2eb;
                        color:#4f745b;
                        border:1px solid #c8ddce;
                    "
                >

                    Transaksi
                    <strong>
                        #<?php echo str_pad($id_berhasil, 5, '0', STR_PAD_LEFT); ?>
                    </strong>
                    berhasil disimpan.

                </div>

            <?php endif; ?>

            <div class="table">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Tanggal</th>

                            <th>Pelanggan</th>

                            <th>Kasir</th>

                            <th>Total</th>

                            <th>Bayar</th>

                            <th>Kembalian</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php
                        $nomor = 1;
                        ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>

                            <tr>

                                <td>
                                    <?php echo $nomor++; ?>
                                </td>

                                <td>
                                    <?php
                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $data['tanggal_penjualan']
                                        )
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $data['nama_pelanggan']
                                        ?: 'Pelanggan Umum'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $data['nama_kasir']
                                        ?: '-'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <strong>
                                        Rp
                                        <?php
                                        echo number_format(
                                            $data['total_harga'],
                                            0,
                                            ',',
                                            '.'
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td>
                                    Rp
                                    <?php
                                    echo number_format(
                                        $data['uang_bayar'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </td>

                                <td>
                                    Rp
                                    <?php
                                    echo number_format(
                                        $data['kembalian'],
                                        0,
                                        ',',
                                        '.'
                                    );
                                    ?>
                                </td>

                                <td class="actions">

                                    <a
                                        href="detail_penjualan.php?id=<?php echo $data['penjualan_id']; ?>"
                                        class="action edit"
                                    >
                                        Detail
                                    </a>

                                    <a
                                        href="cetak_nota.php?id=<?php echo $data['penjualan_id']; ?>"
                                        class="action print"
                                        target="_blank"
                                    >
                                        Cetak Nota
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="8"
                                style="text-align:center;padding:30px;"
                            >
                                Belum ada transaksi penjualan.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <?php include '../includes/footer.php'; ?>

    </div>

</div>

</body>
</html>