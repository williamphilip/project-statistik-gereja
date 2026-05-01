<?php 
// Memulai session
session_start();

// Menghapus semua data session
session_destroy();

// Mengarahkan pengguna kembali ke halaman login dengan pesan sukses
header("location:login.php?pesan=logout");
exit();
?>