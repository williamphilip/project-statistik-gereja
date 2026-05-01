<?php
session_start();
include 'koneksi.php';

if ($_SESSION['role'] != 'admin') {
    header("location:jemaat.php?pesan=tidak_ada_akses");
    exit();
}

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM jemaat WHERE id='$id'");

header("location:jemaat.php?pesan=hapus_berhasil");
?>