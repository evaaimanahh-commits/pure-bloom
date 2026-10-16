<?php
session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');

// Proses hapus pelanggan
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $query_hapus = mysqli_query(
        $koneksi,
        "DELETE FROM pelanggan WHERE pelanggan_id = $id"
    );

    header("Location: pelanggan.php");
    exit();
}

// Ambil data pelanggan
$query_pelanggan = mysqli_query(
    $koneksi,
    "SELECT * FROM pelanggan ORDER BY pelanggan_id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pelanggan - Pure Bloom</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="app">

    <?php include '../includes/sidebar.php'; ?>

    <main class="main">

        <?php include '../includes/header.php'; ?>

        <div class="content">

            <!-- Judul halaman -->
            <div class="title">
                <div>
                    <h1>Pelanggan</h1>
                    <p>Kelola data pelanggan Pure Bloom.</p>
                </div>

                <a href="pelanggan_tambah.php" class="btn">
                    + Tambah Pelanggan
                </a>
            </div>


            <!-- Data pelanggan -->
            <div class="card">

                <div class="table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Nomor Telepon</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
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
                                        <?php echo htmlspecialchars($data['nama_pelanggan']); ?>
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

                                    <td>

                                        <div class="actions">

                                            <a
                                                href="pelanggan_edit.php?id=<?php echo $data['pelanggan_id']; ?>"
                                                class="btn btn-edit"
                                            >
                                                Edit
                                            </a>

                                            <a
                                                href="pelanggan.php?hapus=<?php echo $data['pelanggan_id']; ?>"
                                                class="btn btn-delete"
                                                onclick="return confirm('Yakin ingin menghapus pelanggan ini?')"
                                            >
                                                Hapus
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php
                                }

                            } else {
                            ?>

                                <tr>
                                    <td colspan="5" style="text-align:center;">
                                        Belum ada data pelanggan.
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