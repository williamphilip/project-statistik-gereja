<?php
session_start();
include 'koneksi.php';

if($_SESSION['status'] != "login") header("location:login.php");
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Sekolah Minggu - Korps Makassar</title>
</head>
<body class="bg-gray-100 flex">
    <main class="flex-1 p-8">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8">Data Anak Sekolah Minggu</h2>
        <a href="dashboard.php" class="flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 transition border border-slate-300 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>
        <!-- Form Input Anak Baru -->
        <div class="bg-white p-6 rounded-xl shadow-md mb-8 gap-4">
            <h3 class="text-xl font-bold mb-4 text-gray-700">Tambah Anak</h3>
            <form action="proses_asm.php" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="nama" placeholder="Nama Lengkap" class="border p-2 rounded-lg outline-none" required>
                <select name="jk" class="border p-2 rounded-lg outline-none" required>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
                <input type="date" name="tgl_lahir" class="border p-2 rounded-lg outline-none" required>
                <input type="text" name="ortu" placeholder="Nama Orang Tua" class="border p-2 rounded-lg outline-none" required>
                <button type="submit" class="bg-yellow-500 text-white font-bold p-2 rounded-lg hover:bg-yellow-600 shadow-md">Simpan</button>
            </form>
        </div>

        <!-- Tabel Data Anak -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
          <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "hapus_berhasil"): ?>
              <div class="bg-red-100 text-red-600 p-3 rounded-lg text-sm mb-4 border border-red-200">
                  Data anak telah berhasil dihapus dari sistem.
              </div>
          <?php endif; ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 font-bold uppercase text-xs">
                        <th class="p-3 border-b">Nama</th>
                        <th class="p-3 border-b">Umur</th>
                        <th class="p-3 border-b">Orang Tua</th>
                        <th class="p-3 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM anak_sekolah_minggu ORDER BY nama_lengkap ASC");
                    while($d = mysqli_fetch_array($q)){
                        $lahir = new DateTime($d['tanggal_lahir']);
                        $umur = (new DateTime())->diff($lahir)->y;
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium"><?= $d['nama_lengkap'] ?> (<?= $d['jenis_kelamin'] ?>)</td>
                        <td class="p-3"><?= $umur ?> Tahun</td>
                        <td class="p-3"><?= $d['nama_orang_tua'] ?></td>
                        <td class="p-3">
                            <a href="hapus_asm.php?id=<?= $d['id'] ?>" class="text-red-500 hover:underline" onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>