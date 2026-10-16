<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';
include '../includes/aktivitas.php';

cek_role('kasir');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transaksi.php');
    exit();
}


$pelanggan_id = isset($_POST['pelanggan_id']) && $_POST['pelanggan_id'] !== ''
    ? (int) $_POST['pelanggan_id']
    : null;


$uang_bayar = isset($_POST['uang_bayar'])
    ? (float) $_POST['uang_bayar']
    : 0;


$diskon = isset($_POST['diskon'])
    ? (float) $_POST['diskon']
    : 0;


$produk_id = $_POST['produk_id'] ?? [];
$jumlah_produk = $_POST['jumlah_produk'] ?? [];


if (empty($produk_id) || empty($jumlah_produk)) {
    die('Produk transaksi belum dipilih.');
}


if (count($produk_id) !== count($jumlah_produk)) {
    die('Data produk dan jumlah tidak sesuai.');
}


/*
 * ========================================
 * CEK DISKON
 * ========================================
 *
 * Pelanggan Umum tidak mendapat diskon.
 */

if ($pelanggan_id === null) {

    $diskon = 0;

} else {

    if ($diskon < 0) {
        $diskon = 0;
    }

}


mysqli_begin_transaction($koneksi);


try {

    $total_harga = 0;

    $detail = [];


    /*
     * Cek semua produk terlebih dahulu
     */

    for ($i = 0; $i < count($produk_id); $i++) {

        $id_produk = (int) $produk_id[$i];

        $jumlah = (int) $jumlah_produk[$i];


        if ($id_produk <= 0 || $jumlah <= 0) {

            throw new Exception(
                'Produk atau jumlah produk tidak valid.'
            );

        }


        $query_produk = mysqli_prepare(
            $koneksi,
            "SELECT
                produk_id,
                nama_produk,
                harga_jual,
                stok
             FROM produk
             WHERE produk_id = ?
             FOR UPDATE"
        );


        if (!$query_produk) {

            throw new Exception(
                'Gagal menyiapkan query produk: ' .
                mysqli_error($koneksi)
            );

        }


        mysqli_stmt_bind_param(
            $query_produk,
            'i',
            $id_produk
        );


        mysqli_stmt_execute($query_produk);


        $hasil_produk =
            mysqli_stmt_get_result(
                $query_produk
            );


        $data_produk =
            mysqli_fetch_assoc(
                $hasil_produk
            );


        mysqli_stmt_close(
            $query_produk
        );


        if (!$data_produk) {

            throw new Exception(
                'Produk tidak ditemukan.'
            );

        }


        $stok =
            (int) $data_produk['stok'];


        $harga =
            (float) $data_produk['harga_jual'];


        if ($jumlah > $stok) {

            throw new Exception(
                'Stok produk "' .
                $data_produk['nama_produk'] .
                '" tidak mencukupi. Stok tersedia: ' .
                $stok
            );

        }


        $subtotal =
            $harga * $jumlah;


        $total_harga += $subtotal;


        $detail[] = [

            'produk_id' => $id_produk,

            'jumlah' => $jumlah,

            'harga' => $harga,

            'subtotal' => $subtotal

        ];

    }


    /*
     * ========================================
     * VALIDASI DISKON
     * ========================================
     */

    if ($diskon > $total_harga) {

        throw new Exception(
            'Diskon tidak boleh lebih besar dari subtotal.'
        );

    }


    /*
     * ========================================
     * HITUNG TOTAL SETELAH DISKON
     * ========================================
     */

    $total_bayar =
        $total_harga - $diskon;


    /*
     * ========================================
     * CEK PEMBAYARAN
     * ========================================
     */

    if ($uang_bayar < $total_bayar) {

        throw new Exception(
            'Uang bayar kurang. Total transaksi setelah diskon Rp ' .
            number_format(
                $total_bayar,
                0,
                ',',
                '.'
            )
        );

    }


    $kembalian =
        $uang_bayar - $total_bayar;


    /*
     * ========================================
     * SIMPAN TRANSAKSI UTAMA
     * ========================================
     */

    $query_penjualan = mysqli_prepare(
        $koneksi,
        "INSERT INTO penjualan
        (
            tanggal_penjualan,
            pelanggan_id,
            user_id,
            total_harga,
            diskon,
            uang_bayar,
            kembalian
        )
        VALUES
        (
            NOW(),
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )"
    );


    if (!$query_penjualan) {

        throw new Exception(
            'Gagal menyiapkan transaksi: ' .
            mysqli_error($koneksi)
        );

    }


    $user_id =
        (int) $_SESSION['user_id'];


    mysqli_stmt_bind_param(
        $query_penjualan,
        'iidddd',
        $pelanggan_id,
        $user_id,
        $total_bayar,
        $diskon,
        $uang_bayar,
        $kembalian
    );


    if (
        !mysqli_stmt_execute(
            $query_penjualan
        )
    ) {

        throw new Exception(
            'Gagal menyimpan transaksi: ' .
            mysqli_stmt_error(
                $query_penjualan
            )
        );

    }


    $penjualan_id =
        mysqli_insert_id($koneksi);


    mysqli_stmt_close(
        $query_penjualan
    );


    if (!$penjualan_id) {

        throw new Exception(
            'ID penjualan tidak berhasil dibuat.'
        );

    }


    /*
     * ========================================
     * SIMPAN DETAIL + KURANGI STOK
     * ========================================
     */

    foreach ($detail as $item) {

        $query_detail = mysqli_prepare(
            $koneksi,
            "INSERT INTO detail_penjualan
            (
                penjualan_id,
                produk_id,
                jumlah_produk,
                harga_satuan,
                subtotal
            )
            VALUES
            (?, ?, ?, ?, ?)"
        );


        if (!$query_detail) {

            throw new Exception(
                'Gagal menyiapkan detail transaksi: ' .
                mysqli_error($koneksi)
            );

        }


        mysqli_stmt_bind_param(
            $query_detail,
            'iiidd',
            $penjualan_id,
            $item['produk_id'],
            $item['jumlah'],
            $item['harga'],
            $item['subtotal']
        );


        if (
            !mysqli_stmt_execute(
                $query_detail
            )
        ) {

            throw new Exception(
                'Gagal menyimpan detail transaksi: ' .
                mysqli_stmt_error(
                    $query_detail
                )
            );

        }


        mysqli_stmt_close(
            $query_detail
        );


        /*
         * Kurangi stok
         */

        $query_stok = mysqli_prepare(
            $koneksi,
            "UPDATE produk
             SET stok = stok - ?
             WHERE produk_id = ?"
        );


        if (!$query_stok) {

            throw new Exception(
                'Gagal menyiapkan update stok: ' .
                mysqli_error($koneksi)
            );

        }


        mysqli_stmt_bind_param(
            $query_stok,
            'ii',
            $item['jumlah'],
            $item['produk_id']
        );


        if (
            !mysqli_stmt_execute(
                $query_stok
            )
        ) {

            throw new Exception(
                'Gagal mengurangi stok: ' .
                mysqli_stmt_error(
                    $query_stok
                )
            );

        }


        mysqli_stmt_close(
            $query_stok
        );

    }


    /*
     * ========================================
     * SEMUA BERHASIL
     * ========================================
     */


    /*
     * Catat aktivitas kasir
     *
     * Dilakukan setelah transaksi,
     * detail, dan stok berhasil diproses.
     */

    catat_aktivitas(
        $koneksi,
        'SIMPAN_TRANSAKSI',
        'Kasir membuat transaksi #' . $penjualan_id
    );


    mysqli_commit($koneksi);


    header(
        'Location: penjualan.php?berhasil=1&id=' .
        $penjualan_id
    );

    exit();


} catch (Exception $e) {

    mysqli_rollback($koneksi);


    die(
        '<div style="
            font-family:Arial;
            max-width:650px;
            margin:50px auto;
            padding:25px;
            background:#fff5f5;
            border:1px solid #e8b4b4;
            border-radius:15px;
            color:#7d3030;
        ">

            <h2>Transaksi Gagal</h2>

            <p>' .
            htmlspecialchars(
                $e->getMessage()
            ) .
            '</p>

            <a
                href="transaksi.php"
                style="
                    display:inline-block;
                    margin-top:10px;
                    padding:10px 15px;
                    background:#9d626f;
                    color:white;
                    text-decoration:none;
                    border-radius:8px;
                "
            >
                Kembali ke Transaksi
            </a>

        </div>'
    );

}

?>