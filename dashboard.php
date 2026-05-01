<?php
session_start();
include 'koneksi.php';
if($_SESSION['status'] != "login"){
    header("location:login.php?pesan=belum_login");
}
$role = $_SESSION['role'];


// Ambil parameter filter ibadah (default: Ibadah Kesucian)
$filter_ibadah = isset($_GET['filter_ibadah']) ? $_GET['filter_ibadah'] : 'Ibadah Kesucian';


// 1. Tangkap input dari Select (Jika tidak ada, default ke 'Semua')
$filter_ibadah = isset($_GET['filter_ibadah']) ? $_GET['filter_ibadah'] : 'Semua';

// 2. Tentukan Kondisi WHERE
if ($filter_ibadah == 'Semua' || $filter_ibadah == '') {
    // Jika Semua, tidak perlu filter jenis_ibadah
    $where_clause = ""; 
} else {
    // Jika pilih spesifik, filter berdasarkan jenis_ibadah
    $where_clause = "WHERE jenis_ibadah = '$filter_ibadah'";
}

// 3. Query Database dengan Filter
$query_tren = mysqli_query($conn, "SELECT * FROM (
    SELECT * FROM laporan_mingguan 
    $where_clause
    ORDER BY tanggal_ibadah DESC LIMIT 7
) AS sub ORDER BY tanggal_ibadah ASC");

$labels = [];
$data_kehadiran = [];
$data_kolekte = [];

while ($row = mysqli_fetch_assoc($query_tren)) {
    // Jika pilih 'Semua', tambahkan nama ibadah di label agar jelas
    $label_name = ($filter_ibadah == 'Semua') ? " (" . $row['jenis_ibadah'] . ")" : "";
    
    $labels[] = date('d M', strtotime($row['tanggal_ibadah'])) . $label_name;
    $data_kehadiran[] = $row['jumlah_kehadiran'];
    $data_kolekte[] = $row['total_kolekte'];
}

// Antisipasi data kosong
if (empty($labels)) {
    $labels = ['No Data'];
    $data_kehadiran = [0];
    $data_kolekte = [0];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Statistik Korps Makassar - Dashboard</title>
</head>
<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-800 h-screen sticky top-0 text-white p-6 shadow-xl">
        <h1 class="text-2xl font-bold mb-10 text-orange-400">Korps Makassar</h1>
        <nav class="space-y-4">
            <a href="#" class="block py-2.5 px-4 rounded bg-slate-700">Dashboard</a>
            <a href="jemaat.php" class="block py-2.5 px-4 rounded hover:bg-slate-700 transition">Data Jemaat</a>
            <a href="sekolah_minggu.php" class="block py-2.5 px-4 rounded hover:bg-slate-700 transition">Data Sekolah Minggu</a>
            <a href="logout.php" class="block py-2.5 px-4 text-red-400 hover:bg-red-900 mt-20 transition">Logout</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="mb-8">
            <h2 class="text-3xl font-semibold text-gray-800">Ringkasan Statistik</h2>
            <p class="text-gray-500">Role Anda: <span class="badge bg-slate-200 px-2 py-1 rounded text-sm font-bold uppercase"><?= $role ?></span></p>
        </div>

        <!-- Tombol Download PDF Baru -->
        <div class="flex item-center gap-4">
            <a href="download_pdf.php" target="_blank" class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 shadow-md transition font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Cetak Laporan PDF
            </a>
        </div>

        <!-- Filter Grafik -->
        <div class="mb-6 flex items-center gap-4">
            <label class="font-medium text-gray-700">Lihat Tren Ibadah:</label>
            <form method="GET" action="dashboard.php">
                <select name="filter_ibadah" onchange="this.form.submit()" class="border p-2 rounded-lg bg-white shadow-sm outline-none focus:ring-2 focus:ring-orange-400">
                    <option value="Semua" <?php if($filter_ibadah == 'Semua') echo 'selected'; ?>>-- Semua Ibadah --</option>
                    <option value="Ibadah Kesucian" <?= $filter_ibadah == 'Ibadah Kesucian' ? 'selected' : '' ?>>Ibadah Kesucian</option>
                    <option value="Ibadah Tebusan" <?= $filter_ibadah == 'Ibadah Tebusan' ? 'selected' : '' ?>>Ibadah Tebusan</option>
                    <option value="Ibadah PKP" <?= $filter_ibadah == 'Ibadah PKP' ? 'selected' : '' ?>>Ibadah PKP</option>
                    <option value="Ibadah PKW" <?= $filter_ibadah == 'Ibadah PKW' ? 'selected' : '' ?>>Ibadah PKW</option>
                    <option value="Ibadah Kesucian Mingguan" <?= $filter_ibadah == 'Ibadah Kesucian Mingguan' ? 'selected' : '' ?>>Ibadah Kesucian Mingguan</option>
                    <option value="Ibadah GPS" <?= $filter_ibadah == 'Ibadah GPS' ? 'selected' : '' ?>>Ibadah GPS</option>
                    <option value="Sekolah Minggu" <?= $filter_ibadah == 'Sekolah Minggu' ? 'selected' : '' ?>>Sekolah Minggu</option>
                </select>
            </form>
        </div>

        <!-- Grid Grafik dengan Hak Akses -->
        <div class="grid grid-cols-1 <?= ($role == 'admin' || $role == 'bendahara') ? 'lg:grid-cols-2' : '' ?> gap-6 mb-8">
            
            <!-- Grafik Kehadiran (Semua Role Bisa Lihat) -->
            <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-orange-500">
                <h3 class="text-lg font-bold mb-4 text-gray-700">Tren Kehadiran Jemaat</h3>
                <canvas id="kehadiranChart" height="150"></canvas>
            </div>

            <!-- Grafik Kolekte (Hanya Admin & Bendahara) -->
            <?php if ($role == 'admin' || $role == 'bendahara') : ?>
            <div class="bg-white p-6 rounded-xl shadow-md border-t-4 border-blue-500">
                <h3 class="text-lg font-bold mb-4 text-gray-700">Statistik Kolekte (Rupiah)</h3>
                <canvas id="kolekteChart" height="150"></canvas>
            </div>
            <?php endif; ?>
            
        </div>

        <!-- Form Input Data (Hanya Admin & Sekretaris/Bendahara) -->
        <?php if ($role == 'admin' || $role == 'bendahara' || $role == 'sekretaris') : ?>
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 mb-8">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Input Laporan Ibadah</h3>
            <form action="proses.php" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Input Jenis Ibadah -->
                <select name="jenis_ibadah" class="border p-2 rounded-lg w-full focus:border-orange-500 outline-none" required>
                    <option value="">-- Pilih Ibadah --</option>
                    <option value="Ibadah Kesucian">Ibadah Kesucian</option>
                    <option value="Ibadah Tebusan">Ibadah Tebusan</option>
                    <option value="Ibadah PKP">Ibadah PKP</option>
                    <option value="Ibadah PKW">Ibadah PKW</option>
                    <option value="Ibadah Kesucian Mingguan">Ibadah Kesucian Mingguan</option>
                    <option value="Ibadah GPS">Ibadah GPS</option>
                    <option value="Ibadah Sekolah Minggu">Sekolah Minggu</option>
                </select>
                
                <input type="date" name="tgl" class="border p-2 rounded-lg w-full focus:border-orange-500 outline-none" required>
                <input type="number" name="hadir" placeholder="Jml Hadir" class="border p-2 rounded-lg w-full focus:border-orange-500 outline-none" required>
                
                <!-- Input Kolekte hanya bisa diisi role tertentu jika diinginkan, di sini kita buka untuk semua penginput -->
                <input type="number" name="kolekte" placeholder="Kolekte (Rp)" class="border p-2 rounded-lg w-full focus:border-orange-500 outline-none" required>
                
                <button type="submit" class="bg-orange-500 text-white font-bold p-2 rounded-lg hover:bg-orange-600 transition shadow-md">
                    Simpan
                </button>
            </form>
        </div>
        <?php endif; ?>

    </main>

    <script>
        // Mengonversi array PHP ke array JavaScript
        const labelChart = <?php echo json_encode($labels); ?>;
        const dataHadir = <?php echo json_encode($data_kehadiran); ?>;
        const dataKolekte = <?php echo json_encode($data_kolekte); ?>;

        // Script Grafik Kehadiran
        const ctxHadir = document.getElementById('kehadiranChart').getContext('2d');
            new Chart(ctxHadir, {
                type: 'line',
                data: {
                    labels: labelChart,
                        datasets: [{
                        label: 'Jumlah Orang',
                        data: dataHadir,
                        borderColor: 'rgb(249, 115, 22)',
                        backgroundColor: 'rgba(249, 115, 22, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });

        // Script Grafik Kolekte (Hanya dijalankan jika elemennya ada/hak akses terpenuhi)
        const canvasKolekte = document.getElementById('kolekteChart');
            if (canvasKolekte) {
                const ctxKolekte = canvasKolekte.getContext('2d');
                new Chart(ctxKolekte, {
                    type: 'bar',
                    data: {
                        labels: labelChart,
                        datasets: [{
                            label: 'Rupiah (Rp)',
                            data: dataKolekte,
                            backgroundColor: 'rgba(37, 99, 235, 0.7)',
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

        function updateJam() {
        const sekarang = new Date();
        const jam = String(sekarang.getHours()).padStart(2, '0');
        const menit = String(sekarang.getMinutes()).padStart(2, '0');
        const detik = String(sekarang.getSeconds()).padStart(2, '0');
        
        const waktuString = jam + ":" + menit + ":" + detik;
        document.getElementById('jam-realtime').textContent = waktuString;
    }

    // Jalankan fungsi setiap 1 detik (1000 milidetik)
    setInterval(updateJam, 1000);

    // Jalankan langsung saat halaman dimuat agar tidak menunggu 1 detik pertama
    updateJam();
    </script>
</body>
</html>