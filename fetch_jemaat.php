<?php
$conn = mysqli_connect("localhost", "root", "root", "db_gereja");

$wilayah = isset($_GET['wilayah']) ? $_GET['wilayah'] : '';

$query = "SELECT * FROM jemaat";
if ($wilayah != '') {
    $wilayah = mysqli_real_escape_string($conn, $wilayah);
    $query .= " WHERE wilayah = '$wilayah'";
}

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while($j = mysqli_fetch_assoc($result)) {
        echo "<tr class='border-b hover:bg-gray-50'>
                <td class='p-3 font-medium'>{$j['nama_lengkap']}</td>
                <td class='p-3'>{$j['wilayah']}</td>
                <td class='p-3'>
                    <span class='px-2 py-1 rounded-full text-xs bg-green-100 text-green-700'>{$j['status']}</span>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3' class='p-3 text-center text-gray-500'>Data tidak ditemukan</td></tr>";
}
?>