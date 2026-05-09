<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:login.php");
    exit();
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Sekolah Minggu - Korps Makassar</title>
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
    <main class="flex-1 w-full p-4 md:p-8">
        <h2 class="text-3xl font-semibold text-gray-800 mb-8">Data Anak Sekolah Minggu</h2>
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
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-700">Daftar Anak</h3>
                <input type="text" id="cariASM" onkeyup="filterTabelASM()" placeholder="Cari nama atau orang tua..." 
                    class="border p-2 rounded-md text-sm outline-none focus:ring-2 focus:ring-yellow-400 w-64 shadow-sm">
            </div>
            <table class="w-full text-left border-collapse" id="tabelDataASM">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 font-bold uppercase text-xs">
                        <th class="p-3 border-b">Nama</th>
                        <th class="p-3 border-b">Tanggal Lahir</th>
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

                        if(!empty($d['tanggal_lahir']) && $d['tanggal_lahir'] != '0000-00-00'){
                            $lahir = new DateTime($d['tanggal_lahir']);
                            $umur = (new DateTime())->diff($lahir)->y . " Tahun";

                            // Format Tanggal Lahir untuk ditampilkan (Contoh: 15 Jan 2018)
                            $tgl_indo = date('d F Y', strtotime($d['tanggal_lahir']));
                        }
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 font-medium"><?= $d['nama_lengkap'] ?> (<?= $d['jenis_kelamin'] ?>)</td>
                        <td class="p-3 text-gray-600"><?= $tgl_indo ?></td>
                        <td class="p-3"><?= $umur ?> Tahun</td>
                        <td class="p-3"><?= $d['nama_orang_tua'] ?></td>
                        <td class="p-3">
                            <?php if($role == 'admin' || $role == 'sekretaris'): ?>
                                <a href="hapus_asm.php?id=<?= $d['id'] ?>" class="text-red-500 hover:underline" onclick="return confirm('Hapus data ini?')">Hapus</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script>
    function filterTabelASM() {
        let input = document.getElementById("cariASM");
        let filter = input.value.toUpperCase();
        let table = document.getElementById("tabelDataASM");
        let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                // Kita cek Kolom Nama (indeks 0) dan Kolom Orang Tua (indeks 2)
                let tdNama = tr[i].getElementsByTagName("td")[0];
                let tdTgl  = tr[i].getElementsByTagName("td")[1];
                let tdOrtu = tr[i].getElementsByTagName("td")[3];
                
                if (tdNama || tdTgl || tdOrtu) {
                    let txtNama = tdNama.textContent || tdNama.innerText;
                    let txtTgl  = tdTgl.textContent || tdTgl.innerText;
                    let txtOrtu = tdOrtu.textContent || tdOrtu.innerText;
                    
                    if (txtNama.toUpperCase().indexOf(filter) > -1 || txtTgl.toUpperCase().indexOf(filter) > -1 || txtOrtu.toUpperCase().indexOf(filter) > -1) {
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