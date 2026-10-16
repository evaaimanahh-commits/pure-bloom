<?php

session_start();

include 'config/koneksi.php';


// ========================================
// JIKA SUDAH LOGIN
// ========================================

if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: kasir/dashboard.php');
    }

    exit();
}


$error = '';


// ========================================
// PROSES LOGIN
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $password_md5 = md5($password);


    if ($role !== 'admin' && $role !== 'kasir') {

        $error = 'Silakan pilih role terlebih dahulu.';

    } else {

        $query = mysqli_prepare(
            $koneksi,
            "SELECT *
             FROM users
             WHERE username = ?
             AND password = ?
             AND role = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $query,
            'sss',
            $username,
            $password_md5,
            $role
        );

        mysqli_stmt_execute($query);

        $hasil = mysqli_stmt_get_result($query);

        $data = mysqli_fetch_assoc($hasil);


        if ($data) {

            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['role'] = $data['role'];


            if ($data['role'] === 'admin') {

                header('Location: admin/dashboard.php');

            } else {

                header('Location: kasir/dashboard.php');

            }

            exit();

        } else {

            $error = 'Username, password, atau role tidak sesuai.';

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

    <title>Login - Pure Bloom</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body class="login">

    <div class="login-card">

        <div class="logo">
            PB
        </div>

        <h1>Pure Bloom</h1>

        <p>Beauty Store</p>


        <?php if ($error) { ?>

            <div class="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <form method="POST">

            <label for="username">
                Username
            </label>

            <input
                type="text"
                id="username"
                name="username"
                required
            >


            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >


            <label for="role">
                Masuk sebagai
            </label>

            <select
                id="role"
                name="role"
                required
            >

                <option value="">
                    Pilih role
                </option>

                <option value="admin">
                    Admin
                </option>

                <option value="kasir">
                    Kasir
                </option>

            </select>


            <button type="submit">
                Masuk
            </button>

        </form>


        
    </div>

</body>

</html>