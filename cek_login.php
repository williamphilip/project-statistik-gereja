<?php 
session_start();
include 'koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

$login = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$cek = mysqli_num_rows($login);

if($cek > 0){
    $data = mysqli_fetch_assoc($login);

    // Verifikasi password (disarankan menggunakan password_hash di produksi)
    if($password == $data['password']){
        $_SESSION['username'] = $username;
        $_SESSION['role']     = $data['role']; // admin, bendahara, atau sekretaris
        $_SESSION['status']   = "login";
        
        header("location:dashboard.php");
    } else {
        header("location:login.php?pesan=gagal");
    }
} else {
    header("location:login.php?pesan=gagal");
}
?>