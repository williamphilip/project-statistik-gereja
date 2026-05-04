<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_ibadah = mysqli_real_escape_string($conn, $_POST['jenis_ibadah']);
    $tgl          = mysqli_real_escape_string($conn, $_POST['tgl']);
    $hadir        = mysqli_real_escape_string($conn, $_POST['hadir']);
    $kolekte      = mysqli_real_escape_string($conn, $_POST['kolekte']);
    $perpuluhan = $_POST['perpuluhan'] ?? 0; // Jika kosong set ke 0
    $syukur     = $_POST['syukur'] ?? 0;

    $query = "INSERT INTO laporan_mingguan (tanggal_ibadah, jenis_ibadah, jumlah_kehadiran, total_kolekte, perpuluhan, syukur) 
            VALUES ('$tgl', '$jenis_ibadah', '$hadir', '$kolekte', '$perpuluhan', '$syukur')";

    if (mysqli_query($conn, $query)) {
        header("location:dashboard.php?pesan=input_berhasil");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>