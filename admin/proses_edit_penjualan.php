<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


/*
 * AMBIL DATA POST
 */

$penjualan_id = isset($_POST['penjualan_id'])
    ? (int) $_POST['penjualan_id']
    : 0;

$pelanggan_id = isset($_POST['pelanggan_id'])
    && $_POST['pelanggan_id'] !== ''
    ? (int) $_POST['pelanggan_id']
    : null;

$diskon = isset($_POST['diskon'])
    ? (float) $_POST['diskon']
    : 0;

$uang_bayar = isset($_POST['uang_bayar'])
    ? (float) $_POST['uang_bayar']
    : 0;

$produk_id =
    $_POST['produk_id'] ?? [];

$jumlah_produk =
    $_POST['jumlah_produk'] ?? [];


if ($penjualan_id <= 0) {
    die('ID transaksi tidak valid.');
}


if (count($produk_id) === 0) {
    die('Produk transaksi tidak boleh kosong.');
}


/*
 * DISKON
 * HANYA UNTUK PELANGGAN TERDAFTAR
 */

if ($pelanggan_id === null) {
    $diskon = 0;
}


/*
 * MULAI TRANSAKSI DATABASE
 */

mysqli_begin_transaction($koneksi);

try {


    /*
     * AMBIL DETAIL LAMA
     */

    $query_lama = mysqli_prepare(
        $koneksi,
        "SELECT
            produk_id,
            jumlah_produk
        FROM detail_penjualan
        WHERE penjualan_id = ?"
    );

    mysqli_stmt_bind_param(
        $query_lama,
        'i',
        $penjualan_id
    );

    mysqli_stmt_execute($query_lama);

    $hasil_lama =
        mysqli_stmt_get_result(
            $query_lama
        );


    /*
     * KEMBALIKAN STOK LAMA
     */

    while (
        $lama =
        mysqli_fetch_assoc($hasil_lama)
    ) {

        $update_stok =
            mysqli_prepare(
                $koneksi,
                "UPDATE produk
                 SET stok = stok + ?
                 WHERE produk_id = ?"
            );

        mysqli_stmt_bind_param(
            $update_stok,
            'ii',
            $lama['jumlah_produk'],
            $lama['produk_id']
        );

        mysqli_stmt_execute(
            $update_stok
        );

        mysqli_stmt_close(
            $update_stok
        );

    }

    mysqli_stmt_close($query_lama);


    /*
     * HAPUS DETAIL LAMA
     */

    $hapus_detail =
        mysqli_prepare(
            $koneksi,
            "DELETE FROM detail_penjualan
             WHERE penjualan_id = ?"
        );

    mysqli_stmt_bind_param(
        $hapus_detail,
        'i',
        $penjualan_id
    );

    mysqli_stmt_execute(
        $hapus_detail
    );

    mysqli_stmt_close(
        $hapus_detail
    );


    /*
     * HITUNG TOTAL BARU
     */

    $total_sebelum_diskon = 0;

    $detail_baru = [];


    foreach (
        $produk_id as $index => $id_produk
    ) {

        $id_produk =
            (int) $id_produk;

        $jumlah =
            isset(
                $jumlah_produk[$index]
            )
            ? (int) $jumlah_produk[$index]
            : 0;


        if (
            $id_produk <= 0 ||
            $jumlah <= 0
        ) {

            throw new Exception(
                'Data produk tidak valid.'
            );

        }


        /*
         * AMBIL HARGA DAN STOK
         */

        $query_produk =
            mysqli_prepare(
                $koneksi,
                "SELECT
                    nama_produk,
                    harga_jual,
                    stok
                FROM produk
                WHERE produk_id = ?
                FOR UPDATE"
            );

        mysqli_stmt_bind_param(
            $query_produk,
            'i',
            $id_produk
        );

        mysqli_stmt_execute(
            $query_produk
        );

        $hasil_produk =
            mysqli_stmt_get_result(
                $query_produk
            );

        $produk =
            mysqli_fetch_assoc(
                $hasil_produk
            );

        mysqli_stmt_close(
            $query_produk
        );


        if (!$produk) {

            throw new Exception(
                'Produk tidak ditemukan.'
            );

        }


        if (
            $jumlah >
            (int) $produk['stok']
        ) {

            throw new Exception(
                'Stok produk "' .
                $produk['nama_produk'] .
                '" tidak mencukupi.'
            );

        }


        $harga =
            (float) $produk['harga_jual'];

        $subtotal =
            $harga * $jumlah;


        $total_sebelum_diskon +=
            $subtotal;


        $detail_baru[] = [

            'produk_id' =>
                $id_produk,

            'jumlah' =>
                $jumlah,

            'harga' =>
                $harga,

            'subtotal' =>
                $subtotal

        ];

    }


    /*
     * CEK DISKON
     */

    if (
        $diskon >
        $total_sebelum_diskon
    ) {

        $diskon =
            $total_sebelum_diskon;

    }


    $total_harga =
        $total_sebelum_diskon -
        $diskon;


    /*
     * CEK PEMBAYARAN
     */

    if (
        $uang_bayar <
        $total_harga
    ) {

        throw new Exception(
            'Uang pembayaran kurang.'
        );

    }


    $kembalian =
        $uang_bayar -
        $total_harga;


    /*
     * UPDATE PENJUALAN
     */

    $update_penjualan =
        mysqli_prepare(
            $koneksi,
            "UPDATE penjualan
             SET
                pelanggan_id = ?,
                total_harga = ?,
                diskon = ?,
                uang_bayar = ?,
                kembalian = ?
             WHERE penjualan_id = ?"
        );


    mysqli_stmt_bind_param(
        $update_penjualan,
        'iddddi',
        $pelanggan_id,
        $total_harga,
        $diskon,
        $uang_bayar,
        $kembalian,
        $penjualan_id
    );


    if (
        !mysqli_stmt_execute(
            $update_penjualan
        )
    ) {

        throw new Exception(
            'Gagal memperbarui transaksi.'
        );

    }


    mysqli_stmt_close(
        $update_penjualan
    );


    /*
     * INSERT DETAIL BARU
     */

    foreach (
        $detail_baru as $detail
    ) {

        $insert_detail =
            mysqli_prepare(
                $koneksi,
                "INSERT INTO detail_penjualan
                (
                    penjualan_id,
                    produk_id,
                    jumlah_produk,
                    harga_satuan,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?)"
            );


        mysqli_stmt_bind_param(
            $insert_detail,
            'iiidd',
            $penjualan_id,
            $detail['produk_id'],
            $detail['jumlah'],
            $detail['harga'],
            $detail['subtotal']
        );


        mysqli_stmt_execute(
            $insert_detail
        );


        mysqli_stmt_close(
            $insert_detail
        );


        /*
         * KURANGI STOK BARU
         */

        $update_stok =
            mysqli_prepare(
                $koneksi,
                "UPDATE produk
                 SET stok = stok - ?
                 WHERE produk_id = ?"
            );


        mysqli_stmt_bind_param(
            $update_stok,
            'ii',
            $detail['jumlah'],
            $detail['produk_id']
        );


        mysqli_stmt_execute(
            $update_stok
        );


        mysqli_stmt_close(
            $update_stok
        );

    }


    /*
     * SIMPAN AKTIVITAS
     */

    $nama_user =
        $_SESSION['nama_lengkap'] ?? 'Admin';


    /*
     * CEK APAKAH TABEL AKTIVITAS SUDAH ADA
     *
     * Untuk sementara aktivitas akan dibuat
     * pada bagian fitur nomor 5.
     */


    mysqli_commit($koneksi);


    header(
        'Location: detail_penjualan.php?id=' .
        $penjualan_id .
        '&status=updated'
    );

    exit();


} catch (Exception $e) {

    mysqli_rollback($koneksi);

    die(
        'Gagal memperbarui transaksi: ' .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>