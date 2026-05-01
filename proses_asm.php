<?php
session_start();
include 'koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jk = $_POST['jk'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $ortu = mysqli_real_escape_string($conn, $_POST['ortu']);

    $q = "INSERT INTO anak_sekolah_minggu (nama_lengkap, jenis_kelamin, tanggal_lahir, nama_orang_tua) 
          VALUES ('$nama', '$jk', '$tgl_lahir', '$ortu')";

    if(mysqli_query($conn, $q)){
        header("location:sekolah_minggu.php?pesan=sukses");
    } else {
        echo mysqli_error($conn);
    }
}
?>