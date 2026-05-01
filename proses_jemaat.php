<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jk = mysqli_real_escape_string($conn, $_POST['jk']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $wilayah = mysqli_real_escape_string($conn, $_POST['wilayah']);
    $status = "Aktif";

    $insert = mysqli_query($conn, "INSERT INTO jemaat (nama_lengkap, jenis_kelamin, tanggal_lahir, wilayah, status) 
                                VALUES ('$nama', '$jk', '$tanggal_lahir', '$wilayah', '$status')");

    if ($insert) {
        header("location:jemaat.php?pesan=berhasil");
    } else {
        echo "Gagal menyimpan: " . mysqli_error($conn);
    }
}
?>