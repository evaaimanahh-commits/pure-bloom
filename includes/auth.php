<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// ========================================
// CEK LOGIN
// ========================================

function cek_login()
{
    if (!isset($_SESSION['user_id'])) {

        header('Location: ../login.php');
        exit();
    }
}


// ========================================
// CEK ROLE
// ========================================

function cek_role($role)
{
    cek_login();

    if (
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== $role
    ) {

        if (
            isset($_SESSION['role']) &&
            $_SESSION['role'] === 'admin'
        ) {

            header('Location: ../admin/dashboard.php');

        } else {

            header('Location: ../kasir/dashboard.php');
        }

        exit();
    }
}


// ========================================
// ESCAPE HTML
// ========================================

function e($value)
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}