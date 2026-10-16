<?php

function catat_aktivitas(
    $koneksi,
    $aktivitas,
    $keterangan = ''
) {

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $user_id = (int) $_SESSION['user_id'];

    $query = mysqli_prepare(
        $koneksi,
        "INSERT INTO aktivitas
        (
            user_id,
            aktivitas,
            keterangan
        )
        VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $query,
        'iss',
        $user_id,
        $aktivitas,
        $keterangan
    );

    mysqli_stmt_execute($query);

    mysqli_stmt_close($query);
}