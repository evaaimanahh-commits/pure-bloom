<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// CEK ID PELANGGAN
// ========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pelanggan.php');
    exit();
}

$pelanggan_id = (int) $_GET['id'];


// ========================================
// AMBIL DATA PELANGGAN
// ========================================

$query_pelanggan = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM pelanggan
     WHERE pelanggan_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $query_pelanggan,
    'i',
    $pelanggan_id
);

mysqli_stmt_execute($query_pelanggan);

$hasil_pelanggan = mysqli_stmt_get_result($query_pelanggan);

$data_pelanggan = mysqli_fetch_assoc($hasil_pelanggan);


// Jika data tidak ditemukan
if (!$data_pelanggan) {
    header('Location: pelanggan.php');
    exit();
}


// ========================================
// DATA AWAL
// ========================================

$nama_pelanggan = $data_pelanggan['nama_pelanggan'];
$nomor_telepon = $data_pelanggan['nomor_telepon'];
$alamat = $data_pelanggan['alamat'];

$error = '';


// ========================================
// PROSES UPDATE
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');


    // ========================================
    // VALIDASI
    // ========================================

    if ($nama_pelanggan === '') {

        $error = 'Nama pelanggan wajib diisi.';

    } else {


        // ========================================
        // UPDATE DATA
        // ========================================

        $query_update = mysqli_prepare(
            $koneksi,
            "UPDATE pelanggan
             SET nama_pelanggan = ?,
                 nomor_telepon = ?,
                 alamat = ?
             WHERE pelanggan_id = ?"
        );

        mysqli_stmt_bind_param(
            $query_update,
            'sssi',
            $nama_pelanggan,
            $nomor_telepon,
            $alamat,
            $pelanggan_id
        );


        if (mysqli_stmt_execute($query_update)) {

            header('Location: pelanggan.php');
            exit();

        } else {

            $error = 'Data pelanggan gagal diperbarui.';
        }
    }
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
        Edit Pelanggan - Pure