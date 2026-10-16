<?php
session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');


// ==============================
// PROSES HAPUS PENGGUNA
// ==============================

if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    // Jangan izinkan admin menghapus akun sendiri
    if ($id != $_SESSION['user_id']) {

        $query_hapus = mysqli_query(
            $koneksi,
            "DELETE FROM users WHERE user_id = $id"
        );
    }

    header("Location: pengguna.php");
    exit();
}


// ==============================
// AMBIL DATA PENGGUNA
// ==============================

$query_pengguna = mysqli_query(
    $koneksi,
    "SELECT user_id, username, nama_lengkap, role, created_at
     FROM users
     ORDER BY user_id DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pengguna - Pure Bloom</title>

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


            <!-- ==============================
                 JUDUL HALAMAN
            =============================== -->

            <div class="title">

                <div>

                    <h1>Pengguna</h1>

                    <p>
                        Kelola akun Admin dan Kasir Pure Bloom.
                    </p>

                </div>


                <a
                    href="pengguna_tambah.php"
                    class="btn"
                >
                    + Tambah Pengguna
                </a>

            </div>


            <!-- ==============================
                 TABEL PENGGUNA
            =============================== -->

            <div class="card">

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>

                                <th>Username</th>

                                <th>Nama Lengkap</th>

                                <th>Role</th>

                                <th>Dibuat</th>

                                <th>Aksi</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php

                            $nomor = 1;

                            if (mysqli_num_rows($query_pengguna) > 0) {

                                while ($data = mysqli_fetch_assoc($query_pengguna)) {

                            ?>

                                <tr>

                                    <!-- Nomor -->

                                    <td>
                                        <?php echo $nomor++; ?>
                                    </td>


                                    <!-- Username -->

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $data['username']
                                        );
                                        ?>
                                    </td>


                                    <!-- Nama -->

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $data['nama_lengkap']
                                        );
                                        ?>
                                    </td>


                                    <!-- Role -->

                                    <td>

                                        <?php if ($data['role'] == 'admin') { ?>

                                            <span class="badge badge-admin">
                                                Admin
                                            </span>

                                        <?php } else { ?>

                                            <span class="badge badge-kasir">
                                                Kasir
                                            </span>

                                        <?php } ?>

                                    </td>


                                    <!-- Tanggal dibuat -->

                                    <td>

                                        <?php
                                        echo date(
                                            'd-m-Y H:i',
                                            strtotime($data['created_at'])
                                        );
                                        ?>

                                    </td>


                                    <!-- Aksi -->

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="pengguna_edit.php?id=<?php echo $data['user_id']; ?>"
                                                class="btn btn-edit"
                                            >
                                                Edit
                                            </a>


                                            <?php if ($data['user_id'] != $_SESSION['user_id']) { ?>

                                                <a
                                                    href="pengguna.php?hapus=<?php echo $data['user_id']; ?>"
                                                    class="btn btn-delete"
                                                    onclick="return confirm('Yakin ingin menghapus pengguna ini?')"
                                                >
                                                    Hapus
                                                </a>

                                            <?php } ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php

                                }

                            } else {

                            ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        style="text-align: center;"
                                    >
                                        Belum ada data pengguna.
                                    </td>

                                </tr>

                            <?php

                            }

                            ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <?php include '../includes/footer.php'; ?>

    </main>

</div>

</body>

</html>