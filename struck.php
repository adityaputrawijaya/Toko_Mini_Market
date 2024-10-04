<?php
// Konfigurasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Toko_Mini_Market";

// Membuat koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mendapatkan ID penjualan dari URL atau form
$sale_id = isset($_GET['sale_id']) ? $_GET['sale_id'] : 1;  // Ganti 1 dengan default ID jika tidak ada yang dikirim

// Query untuk mengambil data penjualan
$sql = "
    SELECT
        S.sale_id AS 'Nomor Transaksi',
        S.sale_date AS 'Tanggal Transaksi',
        C.customer_name AS 'Nama Pelanggan',
        E.employee_name AS 'Kasir',
        P.payment_method AS 'Metode Pembayaran',
        P.payment_amount AS 'Jumlah Dibayar',
        SI.product_id AS 'Kode Produk',
        PR.product_name AS 'Nama Produk',
        SI.quantity_sold AS 'Jumlah Barang',
        SI.product_price AS 'Harga Satuan',
        (SI.quantity_sold * SI.product_price) AS 'Total Harga'
    FROM Sales S
    JOIN Customers C ON S.customer_id = C.customer_id
    JOIN Employees E ON S.employee_id = E.employee_id
    JOIN Payments P ON S.sale_id = P.sale_id
    JOIN Sale_Items SI ON S.sale_id = SI.sale_id
    JOIN Products PR ON SI.product_id = PR.product_id
    WHERE S.sale_id = ?
";

// Menyiapkan statement untuk eksekusi query
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$result = $stmt->get_result();

// Menampilkan struk penjualan
if ($result->num_rows > 0) {
    echo "<h2>Struk Penjualan</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Nomor Transaksi</th><th>Tanggal Transaksi</th><th>Nama Pelanggan</th><th>Kasir</th></tr>";

    // Ambil data penjualan pertama
    $first_row = true;
    $total = 0;
    $payment_method = '';
    $payment_amount = 0;

    while ($row = $result->fetch_assoc()) {
        if ($first_row) {
            echo "<tr><td>" . $row['Nomor Transaksi'] . "</td>";
            echo "<td>" . $row['Tanggal Transaksi'] . "</td>";
            echo "<td>" . $row['Nama Pelanggan'] . "</td>";
            echo "<td>" . $row['Kasir'] . "</td></tr>";
            $first_row = false;

            // Store payment method and amount from the first row
            $payment_method = $row['Metode Pembayaran'];
            $payment_amount = $row['Jumlah Dibayar'];
        }

        // Tampilkan item yang dijual
        echo "<tr><td colspan='4'>";
        echo "<strong>Kode Produk:</strong> " . $row['Kode Produk'] . "<br>";
        echo "<strong>Nama Produk:</strong> " . $row['Nama Produk'] . "<br>";
        echo "<strong>Jumlah:</strong> " . $row['Jumlah Barang'] . "<br>";
        echo "<strong>Harga Satuan:</strong> Rp " . number_format($row['Harga Satuan'], 0, ',', '.') . "<br>";
        echo "<strong>Total Harga:</strong> Rp " . number_format($row['Total Harga'], 0, ',', '.');
        echo "</td></tr>";

        $total += $row['Total Harga'];
    }

    echo "<tr><td colspan='4' align='right'><strong>Total Bayar: Rp " . number_format($total, 0, ',', '.') . "</strong></td></tr>";
    echo "</table>";

    // Tampilkan metode pembayaran dan jumlah dibayar
    echo "<h3>Metode Pembayaran: " . $payment_method . "</h3>";
    echo "<h3>Jumlah Dibayar: Rp " . number_format($payment_amount, 0, ',', '.') . "</h3>";
} else {
    echo "Tidak ada data penjualan yang ditemukan.";
}

// Menutup koneksi
$conn->close();
?>
