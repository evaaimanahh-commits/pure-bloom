<header>
    <div>
        <small>BEAUTY STORE</small>
        <h2>Pure Bloom</h2>
    </div>

    <div>
        <b><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></b>
        <br>
        <span><?php echo ucfirst($_SESSION['role']); ?></span>
    </div>
</header>