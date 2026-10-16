<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('kasir');


// ========================================
// AMBIL DATA PELANGGAN
// ========================================

$query_pelanggan = mysqli_query(
    $koneksi,
    "SELECT *
     FROM pelanggan
     ORDER BY pelanggan_id DESC"
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

    <title>Pelanggan - Pure Bloom</title>

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

            <div class="title">

                <div>

                    <h1>Pelanggan</h1>

                    <p>
                        Daftar pelanggan Pure Bloom.
                    </p>

                </div>

            </div>


            <div class="card">

                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Nomor Telepon</th>
                                <th>Alamat</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $nomor = 1;

                            if (mysqli_num_rows($query_pelanggan) > 0) {

                                while ($data = mysqli_fetch_assoc($query_pelanggan)) {

                            ?>

                                <tr>

                                    <td>
                                        <?php echo $nomor++; ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $data['nama_pelanggan']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $data['nomor_telepon'] ?? '-'
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $data['alamat'] ?? '-'
                                        );
                                        ?>
                                    </td>

                                </tr>

                            <?php

                                }

                            } else {

                            ?>

                                <tr>

                                    <td
                                        colspan="4"
                                        style="text-align: center;"
                                    >
                                        Belum ada data pelanggan.
                                    </td>

                                </tr>

                            <?php } ?>

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