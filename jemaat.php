<?php
session_start();
include 'koneksi.php';

// Cek apakah sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php?pesan=belum_login");
    exit();
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Data Jemaat - Korps Makassar</title>
</head>
<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-800 h-screen sticky top-0 text-white p-6 shadow-xl">
        <h1 class="text-2xl font-bold mb-10 text-orange-400">Korps Makassar</h1>
        <nav class="space-y-4">
            <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-slate-700 transition">Dashboard</a>
            <a href="jemaat.php" class="block py-2.5 px-4 rounded bg-slate-700">Data Jemaat</a>
            <a href="sekolah_minggu.php" class="block py-2.5 px-4 rounded bg-slate-700">Data Sekolah Minggu</a>
            <a href="logout.php" class="block py-2.5 px-4 text-red-400 hover:bg-red-900 mt-20 transition">Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8">Manajemen Jemaat</h2>

        <!-- Form Input Jemaat (Hanya muncul untuk Admin & Sekretaris) -->
        <?php if ($role == 'admin' || $role == 'sekretaris') : ?>
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 mb-8">
            <h3 class="text-xl font-bold mb-4 text-gray-700">Tambah Jemaat Baru</h3>
            <form action="proses_jemaat.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="nama" placeholder="Nama Lengkap" class="border p-2 rounded-lg outline-none focus:border-orange-500" required>
                
                <select name="jk" class="border p-2 rounded-lg outline-none focus:border-orange-500" required>
                    <option value="">-- Jenis Kelamin --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>

                <!-- Input Tanggal Lahir Baru -->
                <div class="relative">
                    <input type="date" name="tanggal_lahir" class="w-full border p-2 rounded-lg outline-none focus:border-orange-500" required title="Tanggal Lahir">
                </div>

                <select name="wilayah" class="border p-2 rounded-lg outline-none focus:border-orange-500" required>
                    <option value="">-- Pilih Wilayah --</option>
                    <option value="Utara">Wilayah Utara</option>
                    <option value="Selatan">Wilayah Selatan</option>
                    <option value="Timur">Wilayah Timur</option>
                    <option value="Barat">Wilayah Barat</option>
                </select>

                <button type="submit" class="bg-orange-500 text-white font-bold p-2 rounded-lg hover:bg-orange-600 transition shadow-md">
                    Simpan Jemaat
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Tabel Data Jemaat -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Daftar Jemaat</h3>
                <!-- Input Search Sederhana -->
                <input type="text" id="cariJemaat" onkeyup="filterTabel()" placeholder="Cari nama..." class="border p-2 rounded-md text-sm outline-none focus:ring-2 focus:ring-orange-400">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tabelData">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600">
                            <th class="p-3 border-b">Nama Jemaat</th>
                            <th class="p-3 border-b">L/P</th>
                            <th class="p-3 border-b">Tanggal Lahir</th>
                            <th class="p-3 border-b">Wilayah</th>
                            <th class="p-3 border-b">Status</th>
                            <?php if ($role == 'admin') : ?><th class="p-3 border-b text-center">Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($conn, "SELECT * FROM jemaat ORDER BY nama_lengkap ASC");
                        while($d = mysqli_fetch_array($query)){
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800"><?php echo $d['nama_lengkap']; ?></td>
                            <td class="p-3 text-gray-600"><?php echo $d['jenis_kelamin']; ?></td>
                            <td class="p-3 text-gray-600"><?php echo $d['tanggal_lahir']; ?></td>
                            <td class="p-3 text-gray-600"><?php echo $d['wilayah']; ?></td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 font-semibold"><?php echo $d['status']; ?></span>
                            </td>
                            <?php if ($role == 'admin') : ?>
                            <td class="p-3 text-center space-x-2">
                                <a href="edit_jemaat.php?id=<?php echo $d['id']; ?>" class="text-blue-500 hover:underline">Edit</a>
                                <a href="hapus_jemaat.php?id=<?php echo $d['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Fungsi pencarian client-side sederhana
        function filterTabel() {
            let input = document.getElementById("cariJemaat");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("tabelData");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>