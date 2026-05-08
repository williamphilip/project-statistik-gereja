<?php
session_start();
require 'assets/dompdf/vendor/autoload.php'; // Load Dompdf
include 'koneksi.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$filter_ibadah = isset($_GET['filter_ibadah']) ? mysqli_real_escape_string($conn, $_GET['filter_ibadah']) : 'Semua';

$where_clause = ($filter_ibadah == 'Semua' || $filter_ibadah == '') ? "" : "WHERE jenis_ibadah LIKE '%$filter_ibadah%'";



// --- 1. AMBIL DATA JUMLAH DARI DATABASE ---
// Ambil total jemaat dewasa
$q_jemaat = mysqli_query($conn, "SELECT COUNT(*) as total FROM jemaat");
$res_jemaat = mysqli_fetch_assoc($q_jemaat);
$total_jemaat = $res_jemaat['total'] ?? 0;

// Ambil total anak sekolah minggu
$q_asm = mysqli_query($conn, "SELECT COUNT(*) as total FROM anak_sekolah_minggu");
$res_asm = mysqli_fetch_assoc($q_asm);
$total_asm = $res_asm['total'] ?? 0;

// Pengaturan Dompdf agar bisa membaca gambar/CSS dengan baik
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Ambil data untuk ringkasan jemaat
$total_jemaat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM jemaat"))['total'];
$total_asm = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM anak_sekolah_minggu"))['total'];

// CSS Internal untuk PDF
$html = '
<style>
    body { font-family: "Helvetica", sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
    .header { text-align: center; border-bottom: 2px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 20px; color: #1e293b; }
    .header p { margin: 2px 0; color: #64748b; }
    
    .stats-container { margin-bottom: 20px; width: 100%; }
    .stats-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; width: 45%; display: inline-block; }
    
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th { background-color: #1e293b; color: #ffffff; padding: 8px; text-align: left; text-transform: uppercase; font-size: 10px; }
    td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
    .row-even { background-color: #fcfcfc; }
    
    .category-header { background-color: #f1f5f9; font-weight: bold; padding: 8px; border-left: 4px solid #f97316; margin-bottom: 10px; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .total-section { background-color: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; margin-top: 20px; }
    .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }

    /* Menentukan area footer agar muncul di setiap halaman */
    .footer { 
        position: fixed; 
        bottom: -20px; 
        left: 0; 
        right: 0; 
        height: 50px; 
        text-align: center; 
        font-size: 8px; 
        color: #94a3b8; 
        border-top: 1px solid #e2e8f0; 
        padding-top: 5px; 
    }

    /* Membuat penomoran otomatis */
    .pagenum:before { 
        content: counter(page); 
    }

    <thead>
    <tr style="page-break-inside: avoid; page-break-after: auto;">
        </tr>
    </thead>
</style>

<div class="header">
    <h1>LAPORAN STATISTIK & KEUANGAN GEREJA</h1>
    <p>Kategori: ' . ($filter_ibadah == "Semua" ? "Seluruh Ibadah" : $filter_ibadah) . '</p>
    <p>Periode Laporan: ' . date('F Y') . '</p>
    <p>Dicetak pada: ' . date('d/m/Y') . ' </p>
</div>

<div class="stats-container" style="width: 100%; margin-bottom: 30px;">
    <table style="width: 100%; border: none; margin-bottom: 0;">
        <tr>
            <td style="width: 50%; border: none; padding: 0 10px 0 0;">
                <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 15px; text-align: center; border-radius: 8px;">
                    <strong style="color: #9a3412; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">Jemaat Dewasa</strong><br>
                    <span style="font-size: 24px; font-weight: bold; color: #f97316;">' . number_format($total_jemaat) . ' <small style="font-size: 12px; color: #fb923c;">Jiwa</small></span>
                </div>
            </td>
            <td style="width: 50%; border: none; padding: 0 0 0 10px;">
                <div style="background: #fefce8; border: 1px solid #fef08a; padding: 15px; text-align: center; border-radius: 8px;">
                    <strong style="color: #854d0e; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">Anak Sekolah Minggu</strong><br>
                    <span style="font-size: 24px; font-weight: bold; color: #eab308;">' . number_format($total_asm) . ' <small style="font-size: 12px; color: #facc15;">Anak</small></span>
                </div>
            </td>
        </tr>
    </table>
</div>';

// Pengelompokan berdasarkan Jenis Ibadah
$grand_total_kolekte = 0;
$grand_total_perpuluhan = 0; 
$grand_total_syukur = 0;
$grand_total_hadir = 0;

$jenis_ibadah_q = mysqli_query($conn, "SELECT DISTINCT jenis_ibadah FROM laporan_mingguan $where_clause");

while($j = mysqli_fetch_assoc($jenis_ibadah_q)) {
    $current_ibadah = $j['jenis_ibadah'];
    $html .= '<div class="category-header">Laporan: ' . $current_ibadah . '</div>';
    $html .= '<table>
                <thead>
                    <tr>
                        <th width="15%">Tanggal</th>
                        <th width="10%" class="text-right">Hadir</th>
                        <th width="18%" class="text-right">Kolekte</th>
                        <th width="18%" class="text-right">Perpuluhan</th>
                        <th width="18%" class="text-right">Syukur</th>
                        <th width="21%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    $detail_q = mysqli_query($conn, "SELECT * FROM laporan_mingguan WHERE jenis_ibadah = '$current_ibadah' ORDER BY tanggal_ibadah ASC");
    $sub_kolekte = 0;
    $sub_perpuluhan = 0;
    $sub_syukur = 0;
    $i = 0;

    while($row = mysqli_fetch_assoc($detail_q)) {
        // Hitung Total per Baris
        $total_baris = $row['total_kolekte'] + $row['perpuluhan'] + $row['syukur'];
        
        $bg_class = ($i % 2 == 0) ? '' : 'row-even';
        $html .= '<tr class="'.$bg_class.'">
                    <td>' . date('d/m/y', strtotime($row['tanggal_ibadah'])) . '</td>
                    <td class="text-right">' . number_format($row['jumlah_kehadiran']) . '</td>
                    <td class="text-right">' . number_format($row['total_kolekte'], 0, ',', '.') . '</td>
                    <td class="text-right">' . number_format($row['perpuluhan'], 0, ',', '.') . '</td>
                    <td class="text-right">' . number_format($row['syukur'], 0, ',', '.') . '</td>
                    <td class="text-right font-bold">' . number_format($total_baris, 0, ',', '.') . '</td>
                </tr>';
        
        // Tambahkan ke Subtotal
        $sub_kolekte += $row['total_kolekte'];
        $sub_perpuluhan += $row['perpuluhan'];
        $sub_syukur += $row['syukur'];
        
        $grand_total_hadir += $row['jumlah_kehadiran'];
        $i++;
    }

    $sub_total_ibadah = $sub_kolekte + $sub_perpuluhan + $sub_syukur;

    $html .= '<tr class="font-bold" style="background-color: #f8fafc;">
                <td colspan="2" class="text-right">Subtotal</td>
                <td class="text-right">' . number_format($sub_kolekte, 0, ',', '.') . '</td>
                <td class="text-right">' . number_format($sub_perpuluhan, 0, ',', '.') . '</td>
                <td class="text-right">' . number_format($sub_syukur, 0, ',', '.') . '</td>
                <td class="text-right">Rp ' . number_format($sub_total_ibadah, 0, ',', '.') . '</td>
            </tr>
            </tbody>
        </table>';

    // Tambahkan ke Grand Total Akhir
    $grand_total_kolekte += $sub_kolekte;
    $grand_total_perpuluhan += $sub_perpuluhan;
    $grand_total_syukur += $sub_syukur;

}

// Hitung Grand Total Keseluruhan
$grand_total_penerimaan = $grand_total_kolekte + $grand_total_perpuluhan + $grand_total_syukur;

$html .= '
<div class="total-section">
    <table style="border:none; margin-bottom:0;">
        <tr>
            <td style="border:none;" class="font-bold">TOTAL KEHADIRAN</td>
            <td style="border:none;" class="text-right font-bold">' . number_format($grand_total_hadir) . ' Orang</td>
        </tr>
        <tr>
            <td style="border:none;">Total Kolekte Umum</td>
            <td style="border:none;" class="text-right">Rp ' . number_format($grand_total_kolekte, 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td style="border:none;">Total Persembahan Perpuluhan</td>
            <td style="border:none;" class="text-right">Rp ' . number_format($grand_total_perpuluhan, 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td style="border:none;">Total Persembahan Syukur</td>
            <td style="border:none;" class="text-right">Rp ' . number_format($grand_total_syukur, 0, ',', '.') . '</td>
        </tr>
        <tr style="font-size: 14px; color: #166534;">
            <td style="border:none; padding-top:10px;" class="font-bold">TOTAL PENERIMAAN KESELURUHAN</td>
            <td style="border:none; padding-top:10px;" class="text-right font-bold">Rp ' . number_format($grand_total_penerimaan, 0, ',', '.') . '</td>
        </tr>
    </table>
</div>


<div class="footer">
    Gereja Bala Keselamatan Korps Makassar | 
    Halaman <span class="pagenum"></span> | 
    Dicetak oleh: ' . ($_SESSION['username'] ?? 'Admin') . '
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Gereja_Lengkap.pdf", array("Attachment" => 0));
?>