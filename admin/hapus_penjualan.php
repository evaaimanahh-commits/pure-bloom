<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    die(
        'ID transaksi tidak valid.'
    );

}


/*
 * AMBIL DATA TRANSAKSI
 */

$query =
    mysqli_prepare(
        $koneksi,
        "SELECT
            penjualan_id,
            tanggal_penjualan
        FROM penjualan
        WHERE penjualan_id = ?
        LIMIT 1"
    );


mysqli_stmt_bind_param(
    $query,
    'i',
    $id
);


mysqli_stmt_execute($query);


$hasil =
    mysqli_stmt_get_result(
        $query
    );


$penjualan =
    mysqli_fetch_assoc(
        $hasil
    );


mysqli_stmt_close($query);


if (!$penjualan) {

    die(
        'Transaksi tidak ditemukan.'
    );

}


/*
 * BATAS PENGHAPUSAN
 *
 * Hanya transaksi maksimal 1 hari.
 */

$waktu_transaksi =
    strtotime(
        $penjualan['tanggal_penjualan']
    );


$batas_waktu =
    strtotime('-1 day');


if (
    $waktu_transaksi <
    $batas_waktu
) {

    die(
        'Transaksi sudah lebih dari 1 hari dan tidak dapat dihapus.'
    );

}


/*
 * MULAI TRANSAKSI
 */

mysqli_begin_transaction($koneksi);


try {


    /*
     * AMBIL DETAIL
     */

    $detail =
        mysqli_prepare(
            $koneksi,
            "SELECT
                produk_id,
                jumlah_produk
            FROM detail_penjualan
            WHERE penjualan_id = ?"
        );


    mysqli_stmt_bind_param(
        $detail,
        'i',
        $id
    );


    mysqli_stmt_execute($detail);


    $hasil_detail =
        mysqli_stmt_get_result(
            $detail
        );


    /*
     * KEMBALIKAN STOK
     */

    while (
        $data =
        mysqli_fetch_assoc(
            $hasil_detail
        )
    ) {

        $stok =
            mysqli_prepare(
                $koneksi,
                "UPDATE produk
                 SET stok = stok + ?
                 WHERE produk_id = ?"
            );


        mysqli_stmt_bind_param(
            $stok,
            'ii',
            $data['jumlah_produk'],
            $data['produk_id']
        );


        mysqli_stmt_execute(
            $stok
        );


        mysqli_stmt_close(
            $stok
        );

    }


    mysqli_stmt_close($detail);


    /*
     * HAPUS TRANSAKSI
     *
     * detail_penjualan otomatis terhapus
     * karena ON DELETE CASCADE.
     */

    $hapus =
        mysqli_prepare(
            $koneksi,
            "DELETE FROM penjualan
             WHERE penjualan_id = ?"
        );


    mysqli_stmt_bind_param(
        $hapus,
        'i',
        $id
    );


    mysqli_stmt_execute($hapus);


    mysqli_stmt_close($hapus);


    mysqli_commit($koneksi);


    header(
        'Location: penjualan.php?status=deleted'
    );

    exit();


} catch (Exception $e) {

    mysqli_rollback($koneksi);

    die(
        'Gagal menghapus transaksi: ' .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?><?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    die(
        'ID transaksi tidak valid.'
    );

}


/*
 * AMBIL DATA TRANSAKSI
 */

$query =
    mysqli_prepare(
        $koneksi,
        "SELECT
            penjualan_id,
            tanggal_penjualan
        FROM penjualan
        WHERE penjualan_id = ?
        LIMIT 1"
    );


mysqli_stmt_bind_param(
    $query,
    'i',
    $id
);


mysqli_stmt_execute($query);


$hasil =
    mysqli_stmt_get_result(
        $query
    );


$penjualan =
    mysqli_fetch_assoc(
        $hasil
    );


mysqli_stmt_close($query);


if (!$penjualan) {

    die(
        'Transaksi tidak ditemukan.'
    );

}


/*
 * BATAS PENGHAPUSAN
 *
 * Hanya transaksi maksimal 1 hari.
 */

$waktu_transaksi =
    strtotime(
        $penjualan['tanggal_penjualan']
    );


$batas_waktu =
    strtotime('-1 day');


if (
    $waktu_transaksi <
    $batas_waktu
) {

    die(
        'Transaksi sudah lebih dari 1 hari dan tidak dapat dihapus.'
    );

}


/*
 * MULAI TRANSAKSI
 */

mysqli_begin_transaction($koneksi);


try {


    /*
     * AMBIL DETAIL
     */

    $detail =
        mysqli_prepare(
            $koneksi,
            "SELECT
                produk_id,
                jumlah_produk
            FROM detail_penjualan
            WHERE penjualan_id = ?"
        );


    mysqli_stmt_bind_param(
        $detail,
        'i',
        $id
    );


    mysqli_stmt_execute($detail);


    $hasil_detail =
        mysqli_stmt_get_result(
            $detail
        );


    /*
     * KEMBALIKAN STOK
     */

    while (
        $data =
        mysqli_fetch_assoc(
            $hasil_detail
        )
    ) {

        $stok =
            mysqli_prepare(
                $koneksi,
                "UPDATE produk
                 SET stok = stok + ?
                 WHERE produk_id = ?"
            );


        mysqli_stmt_bind_param(
            $stok,
            'ii',
            $data['jumlah_produk'],
            $data['produk_id']
        );


        mysqli_stmt_execute(
            $stok
        );


        mysqli_stmt_close(
            $stok
        );

    }


    mysqli_stmt_close($detail);


    /*
     * HAPUS TRANSAKSI
     *
     * detail_penjualan otomatis terhapus
     * karena ON DELETE CASCADE.
     */

    $hapus =
        mysqli_prepare(
            $koneksi,
            "DELETE FROM penjualan
             WHERE penjualan_id = ?"
        );


    mysqli_stmt_bind_param(
        $hapus,
        'i',
        $id
    );


    mysqli_stmt_execute($hapus);


    mysqli_stmt_close($hapus);


    mysqli_commit($koneksi);


    header(
        'Location: penjualan.php?status=deleted'
    );

    exit();


} catch (Exception $e) {

    mysqli_rollback($koneksi);

    die(
        'Gagal menghapus transaksi: ' .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>