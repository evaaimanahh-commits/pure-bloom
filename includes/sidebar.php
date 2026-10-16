<aside>

    <div class="brand">
        <img
            src="../assets/images/logo-pure-bloom.png"
            alt="Pure Bloom"
            class="logo-pure-bloom"
        >
    </div>

    <nav>

        <?php if ($_SESSION['role'] === 'admin') { ?>

            <a href="dashboard.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="pelanggan.php">Pelanggan</a>
            <a href="penjualan.php">Penjualan</a>

            <!-- Menu tambahan khusus Admin -->
            <a href="laporan.php">Laporan</a>
            <a href="pengguna.php">Pengguna</a>
            <a href="aktivitas.php">Aktivitas</a>

        <?php } else { ?>

            <a href="dashboard.php">Dashboard</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="produk.php">Produk</a>
            <a href="pelanggan.php">Pelanggan</a>
            <a href="penjualan.php">Penjualan</a>

        <?php } ?>

    </nav>

    <a
        class="logout"
        href="../logout.php"
        onclick="return confirm('Yakin ingin keluar?')"
    >
        Keluar
    </a>

</aside>