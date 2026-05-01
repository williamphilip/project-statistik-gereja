<?php
include 'koneksi.php';

$id             = $_POST['id'];
$nama           = $_POST['nama'];
$tanggal_lahir  = $_POST['tanggal_lahir'];
$wilayah        = $_POST['wilayah']; 
$statusku       = $_POST['status'];  // Mengambil nilai dari select 'status' di form

// Perhatikan penghapusan koma sebelum WHERE dan sinkronisasi nama variabel
$query = "UPDATE jemaat SET 
            nama_lengkap='$nama', 
            tanggal_lahir='$tanggal_lahir', 
            wilayah='$wilayah', 
            status='$statusku' 
        WHERE id='$id'";

$update = mysqli_query($conn, $query);

if ($update) {
    header("location:jemaat.php?pesan=update_berhasil");
} else {
    echo "Gagal memperbarui: " . mysqli_error($conn);
}
?>