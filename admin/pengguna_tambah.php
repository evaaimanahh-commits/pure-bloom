<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ========================================
// VARIABEL
// ========================================

$error = '';

$username = '';
$nama_lengkap = '';
$role = '';


// ========================================
// PROSES TAMBAH PENGGUNA
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $role = $_POST['role'] ?? '';


    // ========================================
    // VALIDASI
    // ========================================

    if ($username === '') {

        $error = 'Username wajib diisi.';

    } elseif ($password === '') {

        $error = 'Password wajib diisi.';

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
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $query_cek,
            's',
            $username
        );

        mysqli_stmt_execute($query_cek);

        $hasil_cek = mysqli_stmt_get_result($query_cek);


        if (mysqli_num_rows($hasil_cek) > 0) {

            $error = 'Username sudah digunakan.';

        } else {


            // ========================================
            // PASSWORD MD5
            // ========================================

            $password_md5 = md5($password);


            // ========================================
            // SIMPAN PENGGUNA
            // ========================================

            $query_simpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO users
                (
                    username,
                    password,
                    nama_lengkap,
                    role
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )"
            );

            mysqli_stmt_bind_param(
                $query_simpan,
                'ssss',
                $username,
                $password_md5,
                $nama_lengkap,
                $role
            );


            if (mysqli_stmt_execute($query_simpan)) {

                header('Location: pengguna.php');
                exit();

            } else {

                $error = 'Pengguna gagal ditambahkan.';
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
        Tambah Pengguna - Pure Bloom
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
                        Tambah Pengguna
                    </h1>

                    <p>
                        Tambahkan akun Admin atau Kasir baru.
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
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            required
                        >

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
                                value=""
                                <?php echo $role === '' ? 'selected' : ''; ?>
                            >
                                -- Pilih Role --
                            </option>

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
                            Simpan Pengguna
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