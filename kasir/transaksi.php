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
     ORDER BY nama_pelanggan ASC"
);


// ========================================
// AMBIL DATA PRODUK
// ========================================

$query_produk = mysqli_query(
    $koneksi,
    "SELECT *
     FROM produk
     WHERE stok > 0
     ORDER BY nama_produk ASC"
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

    <title>
        Transaksi - Pure Bloom
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


            <!-- ========================================
                 JUDUL
            ========================================= -->

            <div class="head">

                <div>

                    <h1>
                        Transaksi Penjualan
                    </h1>

                    <p>
                        Buat transaksi penjualan baru.
                    </p>

                </div>

            </div>


            <!-- ========================================
                 FORM TRANSAKSI
            ========================================= -->

            <form
                method="POST"
                action="proses_transaksi.php"
                id="formTransaksi"
            >


                <!-- ====================================
                     PELANGGAN
                ===================================== -->

                <div class="card">

                    <h2>
                        Informasi Transaksi
                    </h2>


                    <label
                        for="cariPelanggan"
                        style="
                            display:block;
                            margin:14px 0 6px;
                            font-weight:700;
                            font-size:13px;
                        "
                    >
                        Pelanggan
                    </label>


                    <!-- PENCARIAN PELANGGAN -->
                    <input
                        type="search"
                        id="cariPelanggan"
                        placeholder="Cari nama atau nomor telepon pelanggan..."
                        autocomplete="off"
                        style="
                            margin-bottom:10px;
                        "
                    >


                    <select
                        name="pelanggan_id"
                        id="pelanggan_id"
                        onchange="ubahPelanggan()"
                    >

                        <option value="">
                            Pelanggan Umum
                        </option>


                        <?php while (
                            $data_pelanggan =
                            mysqli_fetch_assoc($query_pelanggan)
                        ) { ?>


                            <option
                                value="<?php echo $data_pelanggan['pelanggan_id']; ?>"
                                data-search="<?php
                                    echo htmlspecialchars(
                                        $data_pelanggan['nama_pelanggan']
                                        . ' ' .
                                        ($data_pelanggan['nomor_telepon'] ?? ''),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $data_pelanggan['nama_pelanggan'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                                if (
                                    !empty(
                                        $data_pelanggan['nomor_telepon']
                                    )
                                ) {

                                    echo ' - ';

                                    echo htmlspecialchars(
                                        $data_pelanggan['nomor_telepon'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                }

                                ?>

                            </option>


                        <?php } ?>


                    </select>


                    <small
                        id="infoDiskon"
                        style="
                            display:block;
                            margin-top:8px;
                            color:#7c716d;
                        "
                    >
                        Pelanggan Umum tidak mendapatkan diskon.
                    </small>

                </div>


                <!-- ====================================
                     PRODUK
                ===================================== -->

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
                                Pilih produk yang dibeli pelanggan.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="btn"
                            onclick="tambahProduk()"
                            style="width:auto;"
                        >
                            + Tambah Produk
                        </button>

                    </div>


                    <div id="daftarProduk"></div>


                    <!-- ====================================
                         SUBTOTAL
                    ===================================== -->

                    <div
                        class="total"
                        style="margin-bottom:10px;"
                    >

                        <strong>
                            Subtotal
                        </strong>

                        <strong id="subtotalHarga">
                            Rp 0
                        </strong>

                    </div>


                    <!-- ====================================
                         DISKON
                    ===================================== -->

                    <div
                        style="
                            margin-top:15px;
                            padding:15px;
                            background:var(--cream);
                            border-radius:11px;
                        "
                    >

                        <label
                            for="diskon"
                            style="
                                display:block;
                                margin-bottom:6px;
                                font-weight:700;
                                font-size:13px;
                            "
                        >
                            Diskon Pelanggan
                        </label>


                        <input
                            type="number"
                            name="diskon"
                            id="diskon"
                            min="0"
                            step="1"
                            value="0"
                            placeholder="Masukkan nominal diskon"
                            disabled
                            oninput="hitungTotal()"
                        >


                        <small
                            style="
                                display:block;
                                margin-top:6px;
                                color:var(--muted);
                            "
                        >
                            Diskon hanya dapat diberikan kepada pelanggan terdaftar.
                        </small>

                    </div>


                    <!-- ====================================
                         TOTAL
                    ===================================== -->

                    <div class="total">

                        <strong>
                            Total Belanja
                        </strong>

                        <strong id="totalHarga">
                            Rp 0
                        </strong>

                    </div>

                </div>


                <!-- ====================================
                     PEMBAYARAN
                ===================================== -->

                <div
                    class="card"
                    style="margin-top:20px;"
                >

                    <h2>
                        Pembayaran
                    </h2>


                    <label
                        for="uang_bayar"
                        style="
                            display:block;
                            margin:14px 0 6px;
                            font-weight:700;
                            font-size:13px;
                        "
                    >
                        Uang Bayar
                    </label>


                    <input
                        type="number"
                        name="uang_bayar"
                        id="uang_bayar"
                        min="0"
                        step="1"
                        placeholder="Masukkan uang pembayaran"
                        required
                        oninput="hitungKembalian()"
                    >


                    <div
                        class="total"
                        style="margin-bottom:0;"
                    >

                        <strong>
                            Kembalian
                        </strong>

                        <strong id="kembalian">
                            Rp 0
                        </strong>

                    </div>

                </div>


                <!-- ====================================
                     TOMBOL
                ===================================== -->

                <div
                    class="form-actions"
                    style="margin-top:20px;"
                >

                    <a
                        href="dashboard.php"
                        class="btn soft"
                        style="width:auto;"
                    >
                        Batal
                    </a>


                    <button
                        type="submit"
                        style="width:auto;"
                    >
                        Simpan Transaksi
                    </button>

                </div>


            </form>

        </div>


        <?php include '../includes/footer.php'; ?>


    </main>

</div>


<script>


// ========================================
// PENCARIAN PELANGGAN
// ========================================

const cariPelanggan =
    document.getElementById('cariPelanggan');

const pilihPelanggan =
    document.getElementById('pelanggan_id');


cariPelanggan.addEventListener(
    'input',
    function () {

        const kataKunci =
            this.value
                .toLowerCase()
                .trim();


        const pilihan =
            pilihPelanggan.options;


        for (
            let i = 0;
            i < pilihan.length;
            i++
        ) {

            const option =
                pilihan[i];


            // Pelanggan Umum selalu ditampilkan
            if (option.value === '') {

                option.hidden = false;

                continue;

            }


            const dataPencarian =
                (
                    option.dataset.search || ''
                ).toLowerCase();


            option.hidden =
                !dataPencarian.includes(
                    kataKunci
                );

        }


        // Jika pelanggan yang sedang dipilih
        // tidak sesuai pencarian
        if (
            pilihPelanggan.selectedOptions.length > 0 &&
            pilihPelanggan.selectedOptions[0].hidden
        ) {

            pilihPelanggan.value = '';

            ubahPelanggan();

        }

    }
);


// ========================================
// DATA PRODUK
// ========================================

const daftarProduk = <?php

$data_semua_produk = [];

mysqli_data_seek(
    $query_produk,
    0
);

while (
    $data_produk =
    mysqli_fetch_assoc($query_produk)
) {

    $data_semua_produk[] = [

        'id' =>
            (int) $data_produk['produk_id'],

        'kode' =>
            $data_produk['kode_produk'],

        'nama' =>
            $data_produk['nama_produk'],

        'harga' =>
            (float) $data_produk['harga_jual'],

        'stok' =>
            (int) $data_produk['stok']

    ];

}

echo json_encode(
    $data_semua_produk,
    JSON_UNESCAPED_UNICODE
);

?>;


// ========================================
// FORMAT RUPIAH
// ========================================

function formatRupiah(angka) {

    return 'Rp ' +
        Number(angka).toLocaleString('id-ID');

}


// ========================================
// CEK PELANGGAN
// ========================================

function ubahPelanggan() {

    const pelanggan =
        document.getElementById(
            'pelanggan_id'
        ).value;


    const diskon =
        document.getElementById(
            'diskon'
        );


    const info =
        document.getElementById(
            'infoDiskon'
        );


    if (pelanggan === '') {

        diskon.value = 0;

        diskon.disabled = true;


        info.textContent =
            'Pelanggan Umum tidak mendapatkan diskon.';

    } else {

        diskon.disabled = false;


        info.textContent =
            'Pelanggan terdaftar dapat mendapatkan diskon.';

    }


    hitungTotal();

}


// ========================================
// TAMBAH PRODUK
// ========================================

function tambahProduk() {

    const container =
        document.getElementById(
            'daftarProduk'
        );


    const baris =
        document.createElement(
            'div'
        );


    baris.className = 'row';


    baris.style.gridTemplateColumns =
        '1fr 110px 45px';


    baris.innerHTML = `

        <select
            name="produk_id[]"
            onchange="ubahProduk(this)"
            required
        >

            <option value="">
                -- Pilih Produk --
            </option>


            ${daftarProduk.map(
                produk => `

                <option
                    value="${produk.id}"
                    data-harga="${produk.harga}"
                    data-stok="${produk.stok}"
                >

                    ${produk.kode} -
                    ${produk.nama}
                    |
                    ${formatRupiah(produk.harga)}
                    |
                    Stok: ${produk.stok}

                </option>

            `
            ).join('')}

        </select>


        <input
            type="number"
            name="jumlah_produk[]"
            min="1"
            value="1"
            onchange="hitungTotal()"
            oninput="hitungTotal()"
            required
        >


        <button
            type="button"
            onclick="hapusProduk(this)"
            style="
                width:45px;
                padding:0;
            "
        >
            ×
        </button>

    `;


    container.appendChild(baris);

}


// ========================================
// UBAH PRODUK
// ========================================

function ubahProduk(select) {

    const option =
        select.options[
            select.selectedIndex
        ];


    const stok =
        Number(
            option.dataset.stok || 0
        );


    const row =
        select.parentElement;


    const inputJumlah =
        row.querySelector(
            'input[name="jumlah_produk[]"]'
        );


    inputJumlah.max = stok;


    if (
        Number(inputJumlah.value) > stok
    ) {

        inputJumlah.value = stok;

    }


    hitungTotal();

}


// ========================================
// HAPUS PRODUK
// ========================================

function hapusProduk(button) {

    button.parentElement.remove();

    hitungTotal();

}


// ========================================
// HITUNG SUBTOTAL
// ========================================

function hitungSubtotal() {

    let total = 0;


    const rows =
        document.querySelectorAll(
            '#daftarProduk .row'
        );


    rows.forEach(
        function(row) {

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
                select.selectedIndex > 0
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


                total +=
                    harga * qty;

            }

        }
    );


    return total;

}


// ========================================
// HITUNG TOTAL
// ========================================

function hitungTotal() {

    const subtotal =
        hitungSubtotal();


    const pelanggan =
        document.getElementById(
            'pelanggan_id'
        ).value;


    const diskonInput =
        document.getElementById(
            'diskon'
        );


    let diskon =
        Number(
            diskonInput.value || 0
        );


    // Pelanggan Umum tidak boleh mendapat diskon
    if (pelanggan === '') {

        diskon = 0;

        diskonInput.value = 0;

    }


    // Diskon tidak boleh melebihi subtotal
    if (diskon > subtotal) {

        diskon = subtotal;

        diskonInput.value = subtotal;

    }


    if (diskon < 0) {

        diskon = 0;

        diskonInput.value = 0;

    }


    const total =
        subtotal - diskon;


    document.getElementById(
        'subtotalHarga'
    ).textContent =
        formatRupiah(subtotal);


    document.getElementById(
        'totalHarga'
    ).textContent =
        formatRupiah(total);


    hitungKembalian();

}


// ========================================
// HITUNG KEMBALIAN
// ========================================

function hitungKembalian() {

    const subtotal =
        hitungSubtotal();


    const pelanggan =
        document.getElementById(
            'pelanggan_id'
        ).value;


    let diskon =
        Number(
            document.getElementById(
                'diskon'
            ).value || 0
        );


    if (pelanggan === '') {

        diskon = 0;

    }


    if (diskon > subtotal) {

        diskon = subtotal;

    }


    const total =
        subtotal - diskon;


    const uangBayar =
        Number(
            document.getElementById(
                'uang_bayar'
            ).value || 0
        );


    let kembalian =
        uangBayar - total;


    if (kembalian < 0) {

        kembalian = 0;

    }


    document.getElementById(
        'kembalian'
    ).textContent =
        formatRupiah(kembalian);

}


// ========================================
// VALIDASI SEBELUM SIMPAN
// ========================================

document.getElementById(
    'formTransaksi'
).addEventListener(
    'submit',
    function(event) {


        const rows =
            document.querySelectorAll(
                '#daftarProduk .row'
            );


        if (rows.length === 0) {

            event.preventDefault();


            alert(
                'Tambahkan minimal satu produk.'
            );


            return;

        }


        let total = 0;

        let valid = true;


        rows.forEach(
            function(row) {


                const select =
                    row.querySelector(
                        'select[name="produk_id[]"]'
                    );


                const jumlah =
                    row.querySelector(
                        'input[name="jumlah_produk[]"]'
                    );


                if (
                    !select.value ||
                    Number(jumlah.value) <= 0
                ) {

                    valid = false;

                    return;

                }


                const option =
                    select.options[
                        select.selectedIndex
                    ];


                const harga =
                    Number(
                        option.dataset.harga || 0
                    );


                const stok =
                    Number(
                        option.dataset.stok || 0
                    );


                const qty =
                    Number(
                        jumlah.value
                    );


                if (qty > stok) {

                    valid = false;


                    alert(
                        'Jumlah produk melebihi stok yang tersedia.'
                    );


                    return;

                }


                total +=
                    harga * qty;

            }
        );


        if (!valid) {

            event.preventDefault();

            return;

        }


        const pelanggan =
            document.getElementById(
                'pelanggan_id'
            ).value;


        let diskon =
            Number(
                document.getElementById(
                    'diskon'
                ).value || 0
            );


        // Pelanggan umum = diskon 0
        if (pelanggan === '') {

            diskon = 0;

        }


        if (diskon > total) {

            event.preventDefault();


            alert(
                'Diskon tidak boleh lebih besar dari subtotal.'
            );


            return;

        }


        const totalBayar =
            total - diskon;


        const uangBayar =
            Number(
                document.getElementById(
                    'uang_bayar'
                ).value || 0
            );


        if (uangBayar < totalBayar) {

            event.preventDefault();


            alert(
                'Uang pembayaran masih kurang.'
            );


            return;

        }

    }
);


// ========================================
// PRODUK PERTAMA OTOMATIS
// ========================================

tambahProduk();


</script>


</body>

</html>