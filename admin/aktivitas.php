<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';
include '../includes/aktivitas.php';

cek_role('admin');


$cari =
    isset($_GET['cari'])
    ? trim($_GET['cari'])
    : '';


if ($cari !== '') {

    $cari_sql =
        mysqli_real_escape_string(
            $koneksi,
            $cari
        );


    $query =
        mysqli_query(
            $koneksi,
            "SELECT
                a.aktivitas_id,
                a.aktivitas,
                a.keterangan,
                a.waktu,
                u.nama_lengkap,
                u.role
            FROM aktivitas a
            INNER JOIN users u
                ON a.user_id = u.user_id
            WHERE
                u.nama_lengkap LIKE '%$cari_sql%'
                OR a.aktivitas LIKE '%$cari_sql%'
                OR a.keterangan LIKE '%$cari_sql%'
            ORDER BY a.aktivitas_id DESC"
        );

} else {

    $query =
        mysqli_query(
            $koneksi,
            "SELECT
                a.aktivitas_id,
                a.aktivitas,
                a.keterangan,
                a.waktu,
                u.nama_lengkap,
                u.role
            FROM aktivitas a
            INNER JOIN users u
                ON a.user_id = u.user_id
            ORDER BY a.aktivitas_id DESC"
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

    <title>
        Aktivitas - Pure Bloom
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

            <div class="head">

                <div>

                    <h1>
                        Rekam Aktivitas
                    </h1>

                    <p>
                        Riwayat aktivitas pengguna dalam sistem.
                    </p>

                </div>

            </div>


            <div class="search">

                <form
                    method="GET"
                    style="display:flex;gap:8px;width:100%;"
                >

                    <input
                        type="text"
                        name="cari"
                        value="<?php echo htmlspecialchars($cari); ?>"
                        placeholder="Cari pengguna atau aktivitas..."
                    >

                    <button
                        type="submit"
                        style="width:auto;"
                    >
                        🔎 Cari
                    </button>

                </form>

            </div>


            <div class="table">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Waktu</th>

                            <th>Pengguna</th>

                            <th>Role</th>

                            <th>Aktivitas</th>

                            <th>Keterangan</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $nomor = 1;

                    while (
                        $data =
                        mysqli_fetch_assoc($query)
                    ) {

                    ?>

                        <tr>

                            <td>
                                <?php echo $nomor++; ?>
                            </td>

                            <td>

                                <?php

                                echo date(
                                    'd/m/Y H:i:s',
                                    strtotime(
                                        $data['waktu']
                                    )
                                );

                                ?>

                            </td>

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $data['nama_lengkap']
                                );

                                ?>

                            </td>

                            <td>

                                <?php

                                echo ucfirst(
                                    $data['role']
                                );

                                ?>

                            </td>

                            <td>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $data['aktivitas']
                                    );

                                    ?>

                                </strong>

                            </td>

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $data['keterangan']
                                );

                                ?>

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