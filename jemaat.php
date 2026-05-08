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
<body class="bg-gray-100 lg:flex overflow-x-hidden">

   <div class="lg:hidden bg-slate-800 text-white p-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
        <h1 class="text-xl font-bold text-orange-400">Korps Makassar</h1>
        <button id="hamburgerBtn" class="p-2 focus:outline-none hover:bg-slate-700 rounded transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 text-white p-6 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-0">
        
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-2xl font-bold text-orange-400">Korps Makassar</h1>
            <button id="closeBtn" class="lg:hidden text-gray-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <nav class="space-y-2">
            <a href="dashboard.php" class="block py-2.5 px-4 rounded hover:bg-slate-700 transition">Dashboard</a>
            <a href="jemaat.php" class="block py-2.5 px-4 rounded hover:bg-slate-700 transition">Data Jemaat</a>
            <a href="sekolah_minggu.php" class="block py-2.5 px-4 rounded hover:bg-slate-700 transition">Sekolah Minggu</a>
            <div class="pt-10">
                <a href="logout.php" class="block py-2.5 px-4 text-red-400 hover:bg-red-900/50 rounded transition border border-red-900/20">Logout</a>
            </div>
        </nav>
    </aside>

    <div id="overlay" class="fixed inset-0 bg-black/60 z-40 hidden transition-opacity lg:hidden"></div>

    <!-- Main Content -->
    <main class="flex-1 w-full p-4 md:p-8">
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
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM jemaat ORDER BY nama_lengkap ASC");
                        while($d = mysqli_fetch_array($query)){
                            $tgl_lahir_tampil = "-";

                            if(!empty($d['tanggal_lahir']) && $d['tanggal_lahir'] != '0000-00-00'){
                                // Format Tanggal Lahir: 05 May 2026
                                $tgl_lahir_tampil = date('d M Y', strtotime($d['tanggal_lahir']));
                            }
                        ?>
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800"><?php echo $d['nama_lengkap']; ?></td>
                            <td class="p-3 text-gray-600"><?php echo $d['jenis_kelamin']; ?></td>
                            <td class="p-3 text-gray-600"><?php echo $tgl_lahir_tampil; ?></td>
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
                let tdNama = tr[i].getElementsByTagName("td")[0];
                let tdTgl = tr[i].getElementsByTagName("td")[2];
                let tdStatus = tr[i].getElementsByTagName("td")[3];
                let tdWil = tr[i].getElementsByTagName("td")[4];

                if (tdNama || tdTgl || tdWil) {
                    let txtNama = tdNama.textContent || tdNama.innerText;
                    let txtTgl  = tdTgl.textContent || tdTgl.innerText;
                    let txtWil  = tdWil.textContent || tdWil.innerText;
                    
                        if(
                            txtNama.toUpperCase().indexOf(filter) > -1 || 
                            txtTgl.toUpperCase().indexOf(filter) > -1 || 
                            txtWil.toUpperCase().indexOf(filter) > -1
                        ) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                }
            }
        }

    // sidebar
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const closeBtn = document.getElementById('closeBtn');
    const overlay = document.getElementById('overlay');

    // Fungsi Buka Sidebar
    hamburgerBtn.addEventListener('click', () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    });

    // Fungsi Tutup Sidebar
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }

    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);
    </script>
</body>
</html>