<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');

$cari = isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';

/*
 * PENCARIAN PENJUALAN
 */
if ($cari !== '') {

    $cari_sql = mysqli_real_escape_string(
        $koneksi,
        $cari
    );

    $query = mysqli_query(
        $koneksi,
        "SELECT
            p.penjualan_id,
            p.tanggal_penjualan,
            p.total_harga,
            p.diskon,
            p.uang_bayar,
            p.kembalian,
            pl.nama_pelanggan,
            u.nama_lengkap AS nama_kasir
        FROM penjualan p
        LEFT JOIN pelanggan pl
            ON p.pelanggan_id = pl.pelanggan_id
        LEFT JOIN users u
            ON p.user_id = u.user_id
        WHERE
            p.penjualan_id LIKE '%$cari_sql%'
            OR pl.nama_pelanggan LIKE '%$cari_sql%'
            OR u.nama_lengkap LIKE '%$cari_sql%'
            OR DATE(p.tanggal_penjualan) LIKE '%$cari_sql%'
        ORDER BY p.penjualan_id DESC"
    );

} else {

    $query = mysqli_query(
        $koneksi,
        "SELECT
            p.penjualan_id,
            p.tanggal_penjualan,
            p.total_harga,
            p.diskon,
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

    <title>Penjualan - Pure Bloom</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .search-box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input {
            flex: 1;
        }

        .search-form button {
            width: auto;
        }

        .reset-search {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 16px;
            border-radius: 10px;
            background: var(--cream);
            color: var(--plum);
            font-weight: 700;
        }

        .action-danger {
            background: #fae4e4;
            color: #a44e56;
        }

        @media(max-width:600px) {

            .search-form {
                flex-direction: column;
            }

            .search-form button,
            .reset-search {
                width: 100%;
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
                        Riwayat Penjualan
                    </h1>

                    <p>
                        Kelola dan periksa seluruh transaksi penjualan.
                    </p>

                </div>

            </div>


            <!-- SEARCH -->

            <div class="search-box">

                <form
                    method="GET"
                    class="search-form"
                >

                    <input
                        type="text"
                        name="cari"
                        value="<?php echo htmlspecialchars($cari); ?>"
                        placeholder="Cari ID transaksi, pelanggan, kasir, atau tanggal..."
                    >

                    <button type="submit">
                        🔎 Cari
                    </button>

                    <?php if ($cari !== '') { ?>

                        <a
                            href="penjualan.php"
                            class="reset-search"
                        >
                            Reset
                        </a>

                    <?php } ?>

                </form>

            </div>


            <!-- TABLE -->

            <div class="table">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Tanggal</th>

                            <th>Pelanggan</th>

                            <th>Kasir</th>

                            <th>Diskon</th>

                            <th>Total</th>

                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $nomor = 1;

                    while (
                        $data = mysqli_fetch_assoc($query)
                    ) {

                    ?>

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

                                Rp

                                <?php

                                echo number_format(
                                    $data['diskon'],
                                    0,
                                    ',',
                                    '.'
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

                            <td class="actions">

                                <a
                                    href="detail_penjualan.php?id=<?php echo $data['penjualan_id']; ?>"
                                    class="action edit"
                                >
                                    Detail
                                </a>

                                <a
                                    href="edit_penjualan.php?id=<?php echo $data['penjualan_id']; ?>"
                                    class="action edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="cetak_nota.php?id=<?php echo $data['penjualan_id']; ?>"
                                    class="action print"
                                    target="_blank"
                                >
                                    Cetak
                                </a>

                                <a
                                    href="hapus_penjualan.php?id=<?php echo $data['penjualan_id']; ?>"
                                    class="action action-danger"
                                    onclick="return confirm('Yakin ingin menghapus transaksi ini? Transaksi yang sudah dihapus tidak dapat dikembalikan.')"
                                >
                                    Hapus
                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                    <?php if (mysqli_num_rows($query) === 0) { ?>

                        <tr>

                            <td
                                colspan="7"
                                style="text-align:center;"
                            >
                                Data penjualan tidak ditemukan.
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