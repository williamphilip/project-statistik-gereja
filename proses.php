<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_ibadah = mysqli_real_escape_string($conn, $_POST['jenis_ibadah']);
    $tgl          = mysqli_real_escape_string($conn, $_POST['tgl']);
    $hadir        = mysqli_real_escape_string($conn, $_POST['hadir']);
    $kolekte      = mysqli_real_escape_string($conn, $_POST['kolekte']);

    $query = "INSERT INTO laporan_mingguan (tanggal_ibadah, jenis_ibadah, jumlah_kehadiran, total_kolekte) 
            VALUES ('$tgl', '$jenis_ibadah', '$hadir', '$kolekte')";

    if (mysqli_query($conn, $query)) {
        header("location:dashboard.php?pesan=input_berhasil");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>