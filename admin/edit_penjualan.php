<?php

session_start();

include '../config/koneksi.php';
include '../includes/auth.php';

cek_role('admin');

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    die('ID transaksi tidak valid.');
}


/*
 * DATA TRANSAKSI
 */

$query_penjualan = mysqli_prepare(
    $koneksi,
    "SELECT
        p.*,
        pl.nama_pelanggan
    FROM penjualan p
    LEFT JOIN pelanggan pl
        ON p.pelanggan_id = pl.pelanggan_id
    WHERE p.penjualan_id = ?
    LIMIT 1"
);

mysqli_stmt_bind_param(
    $query_penjualan,
    'i',
    $id
);

mysqli_stmt_execute($query_penjualan);

$hasil_penjualan = mysqli_stmt_get_result(
    $query_penjualan
);

$penjualan = mysqli_fetch_assoc(
    $hasil_penjualan
);

mysqli_stmt_close($query_penjualan);

if (!$penjualan) {
    die('Transaksi tidak ditemukan.');
}


/*
 * PRODUK
 */

$query_produk = mysqli_query(
    $koneksi,
    "SELECT *
     FROM produk
     ORDER BY nama_produk ASC"
);


/*
 * DETAIL TRANSAKSI
 */

$query_detail = mysqli_prepare(
    $koneksi,
    "SELECT
        d.detail_id,
        d.produk_id,
        d.jumlah_produk,
        d.harga_satuan,
        d.subtotal,
        p.kode_produk,
        p.nama_produk,
        p.stok
    FROM detail_penjualan d
    INNER JOIN produk p
        ON d.produk_id = p.produk_id
    WHERE d.penjualan_id = ?
    ORDER BY d.detail_id ASC"
);

mysqli_stmt_bind_param(
    $query_detail,
    'i',
    $id
);

mysqli_stmt_execute($query_detail);

$hasil_detail = mysqli_stmt_get_result(
    $query_detail
);

mysqli_stmt_close($query_detail);


/*
 * PELANGGAN
 */

$query_pelanggan = mysqli_query(
    $koneksi,
    "SELECT *
     FROM pelanggan
     ORDER BY nama_pelanggan ASC"
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

    <title>Edit Transaksi - Pure Bloom</title>

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

                <h1>
                    Edit Transaksi
                </h1>

                <p>
                    Perbaiki transaksi
                    #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?>
                </p>

            </div>


            <form
                method="POST"
                action="proses_edit_penjualan.php"
            >

                <input
                    type="hidden"
                    name="penjualan_id"
                    value="<?php echo $id; ?>"
                >


                <!-- PELANGGAN -->

                <div class="card">

                    <h2>
                        Pelanggan
                    </h2>

                    <label>
                        Pelanggan
                    </label>

                    <select
                        name="pelanggan_id"
                        id="pelanggan_id"
                    >

                        <option value="">
                            Pelanggan Umum
                        </option>

                        <?php while (
                            $pelanggan = mysqli_fetch_assoc(
                                $query_pelanggan
                            )
                        ) { ?>

                            <option
                                value="<?php echo $pelanggan['pelanggan_id']; ?>"
                                <?php

                                if (
                                    $penjualan['pelanggan_id']
                                    == $pelanggan['pelanggan_id']
                                ) {
                                    echo 'selected';
                                }

                                ?>
                            >

                                <?php

                                echo htmlspecialchars(
                                    $pelanggan['nama_pelanggan']
                                );

                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>


                <!-- PRODUK -->

                <div
                    class="card"
                    style="margin-top:20px;"
                >

                    <div class="head">

                        <div>

                            <h2>
                                Produk
                            </h2>

                            <p>
                                Periksa dan perbaiki produk transaksi.
                            </p>

                        </div>

                        <button
                            type="button"
                            onclick="tambahProduk()"
                            style="width:auto;"
                        >
                            + Tambah Produk
                        </button>

                    </div>


                    <div id="daftarProduk">

                    <?php while (
                        $detail = mysqli_fetch_assoc(
                            $hasil_detail
                        )
                    ) { ?>

                        <div
                            class="row"
                            style="grid-template-columns:1fr 110px 45px;"
                        >

                            <select
                                name="produk_id[]"
                                onchange="hitungTotal()"
                                required
                            >

                                <option value="">
                                    -- Pilih Produk --
                                </option>

                                <?php

                                mysqli_data_seek(
                                    $query_produk,
                                    0
                                );

                                while (
                                    $produk = mysqli_fetch_assoc(
                                        $query_produk
                                    )
                                ) {

                                ?>

                                    <option
                                        value="<?php echo $produk['produk_id']; ?>"
                                        data-harga="<?php echo $produk['harga_jual']; ?>"
                                        data-stok="<?php echo $produk['stok']; ?>"
                                        <?php

                                        if (
                                            $produk['produk_id']
                                            == $detail['produk_id']
                                        ) {
                                            echo 'selected';
                                        }

                                        ?>
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $produk['kode_produk']
                                        );

                                        ?>

                                        -

                                        <?php

                                        echo htmlspecialchars(
                                            $produk['nama_produk']
                                        );

                                        ?>

                                    </option>

                                <?php } ?>

                            </select>


                            <input
                                type="number"
                                name="jumlah_produk[]"
                                min="1"
                                value="<?php echo $detail['jumlah_produk']; ?>"
                                required
                                oninput="hitungTotal()"
                            >


                            <button
                                type="button"
                                onclick="hapusProduk(this)"
                                style="width:45px;padding:0;"
                            >
                                ×
                            </button>

                        </div>

                    <?php } ?>

                    </div>

                </div>


                <!-- DISKON -->

                <div
                    class="card"
                    style="margin-top:20px;"
                >

                    <h2>
                        Diskon
                    </h2>

                    <label>
                        Diskon
                    </label>

                    <input
                        type="number"
                        name="diskon"
                        id="diskon"
                        min="0"
                        value="<?php echo (int) $penjualan['diskon']; ?>"
                        oninput="hitungTotal()"
                    >

                    <small>
                        Diskon hanya berlaku untuk pelanggan terdaftar.
                    </small>

                </div>


                <!-- PEMBAYARAN -->

                <div
                    class="card"
                    style="margin-top:20px;"
                >

                    <h2>
                        Pembayaran
                    </h2>

                    <label>
                        Uang Bayar
                    </label>

                    <input
                        type="number"
                        name="uang_bayar"
                        id="uang_bayar"
                        min="0"
                        value="<?php echo (int) $penjualan['uang_bayar']; ?>"
                        required
                        oninput="hitungTotal()"
                    >


                    <div
                        class="total"
                        style="margin-top:20px;"
                    >

                        <strong>
                            Total
                        </strong>

                        <strong id="totalHarga">
                            Rp 0
                        </strong>

                    </div>


                    <div class="total">

                        <strong>
                            Kembalian
                        </strong>

                        <strong id="kembalian">
                            Rp 0
                        </strong>

                    </div>

                </div>


                <!-- BUTTON -->

                <div
                    class="form-actions"
                    style="margin-top:20px;"
                >

                    <a
                        href="penjualan.php"
                        class="btn soft"
                        style="width:auto;"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        style="width:auto;"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

        <?php include '../includes/footer.php'; ?>

    </main>

</div>


<script>

function formatRupiah(angka)
{
    return 'Rp ' +
        Number(angka).toLocaleString('id-ID');
}


function hitungTotal()
{

    let subtotal = 0;

    document
        .querySelectorAll('#daftarProduk .row')
        .forEach(function(row)
        {

            const select =
                row.querySelector(
                    'select[name="produk_id[]"]'
                );

            const jumlah =
                row.querySelector(
                    'input[name="jumlah_produk[]"]'
                );

            if (
                select &&
                select.value &&
                jumlah
            ) {

                const option =
                    select.options[
                        select.selectedIndex
                    ];

                const harga =
                    Number(
                        option.dataset.harga || 0
                    );

                const qty =
                    Number(
                        jumlah.value || 0
                    );

                subtotal += harga * qty;

            }

        });


    const diskon =
        Number(
            document.getElementById(
                'diskon'
            ).value || 0
        );


    const pelanggan =
        document.getElementById(
            'pelanggan_id'
        ).value;


    let total =
        subtotal - diskon;


    if (pelanggan === '') {

        total = subtotal;

    }


    if (total < 0) {

        total = 0;

    }


    document.getElementById(
        'totalHarga'
    ).textContent =
        formatRupiah(total);


    const bayar =
        Number(
            document.getElementById(
                'uang_bayar'
            ).value || 0
        );


    let kembalian =
        bayar - total;


    if (kembalian < 0) {

        kembalian = 0;

    }


    document.getElementById(
        'kembalian'
    ).textContent =
        formatRupiah(kembalian);

}


function tambahProduk()
{

    const container =
        document.getElementById(
            'daftarProduk'
        );


    const row =
        document.createElement('div');


    row.className = 'row';

    row.style.gridTemplateColumns =
        '1fr 110px 45px';


    row.innerHTML = `

        <select
            name="produk_id[]"
            required
            onchange="hitungTotal()"
        >

            <option value="">
                -- Pilih Produk --
            </option>

            <?php

            mysqli_data_seek(
                $query_produk,
                0
            );

            while (
                $produk = mysqli_fetch_assoc(
                    $query_produk
                )
            ) {

            ?>

                <option
                    value="<?php echo $produk['produk_id']; ?>"
                    data-harga="<?php echo $produk['harga_jual']; ?>"
                    data-stok="<?php echo $produk['stok']; ?>"
                >

                    <?php echo htmlspecialchars(
                        $produk['kode_produk']
                    ); ?>

                    -

                    <?php echo htmlspecialchars(
                        $produk['nama_produk']
                    ); ?>

                </option>

            <?php } ?>

        </select>


        <input
            type="number"
            name="jumlah_produk[]"
            min="1"
            value="1"
            required
            oninput="hitungTotal()"
        >


        <button
            type="button"
            onclick="hapusProduk(this)"
            style="width:45px;padding:0;"
        >
            ×
        </button>

    `;


    container.appendChild(row);

}


function hapusProduk(button)
{

    const rows =
        document.querySelectorAll(
            '#daftarProduk .row'
        );


    if (rows.length <= 1) {

        alert(
            'Minimal harus ada satu produk.'
        );

        return;

    }


    button.parentElement.remove();

    hitungTotal();

}


document
    .getElementById('pelanggan_id')
    .addEventListener(
        'change',
        function()
        {

            const diskon =
                document.getElementById(
                    'diskon'
                );

            if (this.value === '') {

                diskon.value = 0;
                diskon.disabled = true;

            } else {

                diskon.disabled = false;

            }

            hitungTotal();

        }
    );


document
    .getElementById('pelanggan_id')
    .dispatchEvent(
        new Event('change')
    );


hitungTotal();

</script>

</body>
</html>