<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// CEK ID PENGGUNA
// ========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pengguna.php');
    exit();
}

$user_id = (int) $_GET['id'];


// ========================================
// AMBIL DATA PENGGUNA
// ========================================

$query_pengguna = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $query_pengguna,
    'i',
    $user_id
);

mysqli_stmt_execute($query_pengguna);

$hasil_pengguna = mysqli_stmt_get_result($query_pengguna);

$data_pengguna = mysqli_fetch_assoc($hasil_pengguna);


if (!$data_pengguna) {
    header('Location: pengguna.php');
    exit();
}


// ========================================
// DATA AWAL
// ========================================

$username = $data_pengguna['username'];
$nama_lengkap = $data_pengguna['nama_lengkap'];
$role = $data_pengguna['role'];

$error = '';


// ========================================
// PROSES UPDATE
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';


    // ========================================
    // VALIDASI
    // ========================================

    if ($username === '') {

        $error = 'Username wajib diisi.';

    } elseif ($nama_lengkap === '') {

        $error = 'Nama lengkap wajib diisi.';

    } elseif ($role !== 'admin' && $role !== 'kasir') {

        $error = 'Role pengguna tidak valid.';

    } else {


        // ========================================
        // CEK USERNAME
        // ========================================

        $query_cek = mysqli_prepare(
            $koneksi,
            "SELECT user_id
             FROM users
             WHERE username = ?
             AND user_id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $query_cek,
            'si',
            $username,
            $user_id
        );

        mysqli_stmt_execute($query_cek);

        $hasil_cek = mysqli_stmt_get_result($query_cek);


        if (mysqli_num_rows($hasil_cek) > 0) {

            $error = 'Username sudah digunakan oleh pengguna lain.';

        } else {


            // ========================================
            // JIKA PASSWORD DIISI
            // ========================================

            if ($password !== '') {

                $password_md5 = md5($password);

                $query_update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET username = ?,
                         password = ?,
                         nama_lengkap = ?,
                         role = ?
                     WHERE user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $query_update,
                    'ssssi',
                    $username,
                    $password_md5,
                    $nama_lengkap,
                    $role,
                    $user_id
                );

            } else {


                // ========================================
                // JIKA PASSWORD TIDAK DIISI
                // PASSWORD LAMA TETAP
                // ========================================

                $query_update = mysqli_prepare(
                    $koneksi,
                    "UPDATE users
                     SET username = ?,
                         nama_lengkap = ?,
                         role = ?
                     WHERE user_id = ?"
                );

                mysqli_stmt_bind_param(
                    $query_update,
                    'sssi',
                    $username,
                    $nama_lengkap,
                    $role,
                    $user_id
                );
            }


            // ========================================
            // EKSEKUSI UPDATE
            // ========================================

            if (mysqli_stmt_execute($query_update)) {

                header('Location: pengguna.php');
                exit();

            } else {

                $error = 'Data pengguna gagal diperbarui.';
            }
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
        Edit Pengguna - Pure Bloom
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

            <div class="title">

                <div>

                    <h1>
                        Edit Pengguna
                    </h1>

                    <p>
                        Perbarui data akun Admin atau Kasir.
                    </p>

                </div>

                <a
                    href="pengguna.php"
                    class="btn"
                >
                    Kembali
                </a>

            </div>


            <!-- ========================================
                 PESAN ERROR
            ========================================= -->

            <?php if ($error !== '') { ?>

                <div class="alert">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php } ?>


            <!-- ========================================
                 FORM
            ========================================= -->

            <div class="card">

                <form
                    method="POST"
                    action=""
                >

                    <!-- USERNAME -->

                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($username); ?>"
                            placeholder="Masukkan username"
                            required
                        >

                    </div>


                    <!-- NAMA LENGKAP -->

                    <div class="form-group">

                        <label for="nama_lengkap">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            value="<?php echo htmlspecialchars($nama_lengkap); ?>"
                            placeholder="Masukkan nama lengkap"
                            required
                        >

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">
                            Password Baru
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Kosongkan jika tidak ingin mengubah password"
                        >

                        <small>
                            Kosongkan bagian ini jika password lama tetap digunakan.
                        </small>

                    </div>


                    <!-- ROLE -->

                    <div class="form-group">

                        <label for="role">
                            Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            required
                        >

                            <option
                                value="admin"
                                <?php echo $role === 'admin' ? 'selected' : ''; ?>
                            >
                                Admin
                            </option>

                            <option
                                value="kasir"
                                <?php echo $role === 'kasir' ? 'selected' : ''; ?>
                            >
                                Kasir
                            </option>

                        </select>

                    </div>


                    <!-- TOMBOL -->

                    <div class="actions">

                        <a
                            href="pengguna.php"
                            class="btn"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="btn"
                        >
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>

</html>