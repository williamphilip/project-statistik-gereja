<?php
session_start();
include 'koneksi.php';

// Proteksi halaman: pastikan hanya yang sudah login yang bisa menghapus
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php?pesan=belum_login");
    exit();
}

// Ambil ID dari URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Query hapus data berdasarkan ID
    $query = mysqli_query($conn, "DELETE FROM anak_sekolah_minggu WHERE id = '$id'");

    if ($query) {
        // Jika berhasil, kembali ke halaman sekolah minggu dengan pesan sukses
        header("location:sekolah_minggu.php?pesan=hapus_berhasil");
    } else {
        // Jika gagal, tampilkan error
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    // Jika ID tidak ditemukan di URL, lempar kembali
    header("location:sekolah_minggu.php");
}
?>