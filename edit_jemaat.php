<?php
session_start();
include 'koneksi.php';

// Jika status session bukan login, tendang balik ke login.php
if(!isset($_SESSION['status']) || $_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
    exit();
}

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM jemaat WHERE id='$id'");
$d = mysqli_fetch_array($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Edit Jemaat</title>
</head>
<body class="bg-gray-100 p-10 flex justify-center">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md border-t-4 border-blue-500">
        <h2 class="text-2xl font-bold mb-6">Edit Data Jemaat</h2>
        <form action="update_jemaat.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
            
            <div>
                <label class="block text-sm mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="<?php echo $d['nama_lengkap']; ?>" class="w-full border p-2 rounded-lg outline-none focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= $d['tanggal_lahir'] ?>" class="w-full border p-2 rounded-lg outline-none focus:ring-2 focus:ring-orange-400" required>
            </div>

            <div>
                <label class="block text-sm mb-1">Wilayah</label>
                <select name="wilayah" class="w-full border p-2 rounded-lg">
                    <option value="Utara" <?php if($d['wilayah'] == 'Utara') echo 'selected'; ?>>Utara</option>
                    <option value="Selatan" <?php if($d['wilayah'] == 'Selatan') echo 'selected'; ?>>Selatan</option>
                    <option value="Timur" <?php if($d['wilayah'] == 'Timur') echo 'selected'; ?>>Timur</option>
                    <option value="Barat" <?php if($d['wilayah'] == 'Barat') echo 'selected'; ?>>Barat</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Keanggotaan</label>
                <select name="status" class="w-full border p-2 rounded-lg outline-none focus:ring-2 focus:ring-orange-400" required>
                    <option value="Aktif" <?= ($d['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="Pindah" <?= ($d['status'] == 'Pindah') ? 'selected' : '' ?>>Pindah</option>
                    <option value="Meninggal" <?= ($d['status'] == 'Meninggal') ? 'selected' : '' ?>>Meninggal</option>
                    <option value="Tidak Aktif" <?= ($d['status'] == 'Tidak Aktif') ? 'selected' : '' ?>>Lainnya (Tidak Aktif)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white flex-1 p-2 rounded-lg font-bold">Update</button>
                <a href="jemaat.php" class="bg-gray-200 text-center flex-1 p-2 rounded-lg font-bold">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>