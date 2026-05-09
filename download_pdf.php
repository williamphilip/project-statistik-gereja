<?php
session_start();
require 'assets/dompdf/vendor/autoload.php'; // Load Dompdf
include 'koneksi.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil role dari session
$role = $_SESSION['role'] ?? 'guest';

$filter_ibadah = isset($_GET['filter_ibadah']) ? mysqli_real_escape_string($conn, $_GET['filter_ibadah']) : 'Semua';
$where_clause = ($filter_ibadah == 'Semua' || $filter_ibadah == '') ? "" : "WHERE jenis_ibadah LIKE '%$filter_ibadah%'";

// --- 1. AMBIL DATA STATISTIK ---
$q_jemaat = mysqli_query($conn, "SELECT COUNT(*) as total FROM jemaat");
$total_jemaat = mysqli_fetch_assoc($q_jemaat)['total'] ?? 0;

$q_asm = mysqli_query($conn, "SELECT COUNT(*) as total FROM anak_sekolah_minggu");
$total_asm = mysqli_fetch_assoc($q_asm)['total'] ?? 0;

// Pengaturan Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// CSS Internal
$html = '
<style>
    body { font-family: "Helvetica", sans-serif; font-size: 11px; line-height: 1.5; color: #333; }
    .header { text-align: center; border-bottom: 2px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { margin: 0; font-size: 18px; color: #1e293b; }
    .header p { margin: 2px 0; color: #64748b; }
    
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
    th { background-color: #1e293b; color: #ffffff; padding: 8px; text-align: left; text-transform: uppercase; font-size: 9px; }
    td { padding: 8px; border-bottom: 1px solid #e2e8f0; word-wrap: break-word; }
    .row-even { background-color: #fcfcfc; }
    
    .category-header { background-color: #f1f5f9; font-weight: bold; padding: 8px; border-left: 4px solid #f97316; margin-bottom: 10px; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .total-section { background-color: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; margin-top: 20px; }
    
    .footer { position: fixed; bottom: -20px; left: 0; right: 0; height: 50px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
    .pagenum:before { content: counter(page); }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
</style>

<div class="header">
    <h1>LAPORAN ' . strtoupper($role) . ' - KORPS MAKASSAR</h1>
    <p>Kategori: ' . ($filter_ibadah == "Semua" ? "Seluruh Ibadah" : $filter_ibadah) . '</p>
    <p>Periode: ' . date('F Y') . ' | Dicetak: ' . date('d/m/Y') . '</p>
</div>';

// Statistik hanya muncul untuk Admin & Sekretaris
if ($role == 'admin' || $role == 'sekretaris') {
    $html .= '
    <div class="stats-container" style="margin-bottom: 20px;">
        <table style="border: none;">
            <tr>
                <td style="border: none; padding-right: 10px;">
                    <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 10px; text-align: center; border-radius: 8px;">
                        <strong style="color: #9a3412;">Jemaat Dewasa</strong><br>
                        <span style="font-size: 18px; font-weight: bold; color: #f97316;">' . number_format($total_jemaat) . ' Jiwa</span>
                    </div>
                </td>
                <td style="border: none; padding-left: 10px;">
                    <div style="background: #fefce8; border: 1px solid #fef08a; padding: 10px; text-align: center; border-radius: 8px;">
                        <strong style="color: #854d0e;">Sekolah Minggu</strong><br>
                        <span style="font-size: 18px; font-weight: bold; color: #eab308;">' . number_format($total_asm) . ' Anak</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>';
}

// Logika Akumulasi
$grand_total_kolekte = 0; $grand_total_perpuluhan = 0; $grand_total_syukur = 0; $grand_total_hadir = 0;

$jenis_ibadah_q = mysqli_query($conn, "SELECT DISTINCT jenis_ibadah FROM laporan_mingguan $where_clause");

while($j = mysqli_fetch_assoc($jenis_ibadah_q)) {
    $current_ibadah = $j['jenis_ibadah'];
    $html .= '<div class="category-header">Laporan: ' . $current_ibadah . '</div>';
    $html .= '<table><thead><tr>';

    // HEADER BERDASARKAN ROLE
    if ($role == 'bendahara') {
        $html .= '<th width="20%">Tanggal</th><th width="20%" class="text-right">Kolekte</th><th width="20%" class="text-right">Perpuluhan</th><th width="20%" class="text-right">Syukur</th><th width="20%" class="text-right">Total</th>';
    } elseif ($role == 'sekretaris') {
        $html .= '<th width="30%">Tanggal</th><th width="40%">Jenis Ibadah</th><th width="30%" class="text-right">Hadir</th>';
    } else { // Admin
        $html .= '<th width="15%">Tanggal</th><th width="10%" class="text-right">Hadir</th><th width="18%" class="text-right">Kolekte</th><th width="18%" class="text-right">Perpuluhan</th><th width="18%" class="text-right">Syukur</th><th width="21%" class="text-right">Total</th>';
    }
    
    $html .= '</tr></thead><tbody>';

    $detail_q = mysqli_query($conn, "SELECT * FROM laporan_mingguan WHERE jenis_ibadah = '$current_ibadah' ORDER BY tanggal_ibadah ASC");
    $i = 0;

    while($row = mysqli_fetch_assoc($detail_q)) {
        $total_baris = $row['total_kolekte'] + $row['perpuluhan'] + $row['syukur'];
        $bg_class = ($i % 2 == 0) ? '' : 'row-even';
        
        $html .= '<tr class="'.$bg_class.'">';
        $html .= '<td>' . date('d/m/y', strtotime($row['tanggal_ibadah'])) . '</td>';

        if ($role == 'bendahara') {
            $html .= '<td class="text-right">' . number_format($row['total_kolekte']) . '</td>';
            $html .= '<td class="text-right">' . number_format($row['perpuluhan']) . '</td>';
            $html .= '<td class="text-right">' . number_format($row['syukur']) . '</td>';
            $html .= '<td class="text-right font-bold">' . number_format($total_baris) . '</td>';
        } elseif ($role == 'sekretaris') {
            $html .= '<td>' . $row['jenis_ibadah'] . '</td>';
            $html .= '<td class="text-right">' . number_format($row['jumlah_kehadiran']) . '</td>';
        } else { // Admin
            $html .= '<td class="text-right">' . $row['jumlah_kehadiran'] . '</td>';
            $html .= '<td class="text-right">' . number_format($row['total_kolekte']) . '</td>';
            $html .= '<td class="text-right">' . number_format($row['perpuluhan']) . '</td>';
            $html .= '<td class="text-right">' . number_format($row['syukur']) . '</td>';
            $html .= '<td class="text-right font-bold">' . number_format($total_baris) . '</td>';
        }
        $html .= '</tr>';
        
        $grand_total_kolekte += $row['total_kolekte'];
        $grand_total_perpuluhan += $row['perpuluhan'];
        $grand_total_syukur += $row['syukur'];
        $grand_total_hadir += $row['jumlah_kehadiran'];
        $i++;
    }
    $html .= '</tbody></table>';
}

// RINGKASAN TOTAL BERDASARKAN ROLE
$grand_total_penerimaan = $grand_total_kolekte + $grand_total_perpuluhan + $grand_total_syukur;
$html .= '<div class="total-section"><table style="border:none; margin-bottom:0;">';

if ($role == 'sekretaris' || $role == 'admin') {
    $html .= '<tr><td style="border:none;" class="font-bold">TOTAL KEHADIRAN</td><td style="border:none;" class="text-right font-bold">' . number_format($grand_total_hadir) . ' Orang</td></tr>';
}
if ($role == 'bendahara' || $role == 'admin') {
    $html .= '<tr><td style="border:none;">Total Kolekte Umum</td><td style="border:none;" class="text-right">Rp ' . number_format($grand_total_kolekte) . '</td></tr>';
    $html .= '<tr><td style="border:none;">Total Persembahan Perpuluhan</td><td style="border:none;" class="text-right">Rp ' . number_format($grand_total_perpuluhan) . '</td></tr>';
    $html .= '<tr><td style="border:none;">Total Persembahan Syukur</td><td style="border:none;" class="text-right">Rp ' . number_format($grand_total_syukur) . '</td></tr>';
    $html .= '<tr style="font-size: 13px; color: #166534;"><td style="border:none;" class="font-bold">TOTAL PENERIMAAN KESELURUHAN</td><td style="border:none;" class="text-right font-bold">Rp ' . number_format($grand_total_penerimaan) . '</td></tr>';
}

$html .= '</table></div>';

$html .= '
<div class="footer">
    Korps Makassar | Halaman <span class="pagenum"></span> | Dicetak oleh: ' . ($_SESSION['username'] ?? 'Admin') . '
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Laporan_Gereja_" . $role . ".pdf", array("Attachment" => 0));