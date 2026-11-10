<?php
declare(strict_types=1);
require_once __DIR__ . '/PegawaiTetap.php';
require_once __DIR__ . '/PegawaiKontrak.php';

function rupiah(int $angka): string {
    return 'Rp' . number_format($angka, 0, ',', '.');
}

$daftar = [
    new PegawaiTetap(1, 'Budi Santoso', 5000000),
    new PegawaiKontrak(2, 'Sari Wulandari', 35000, 160),
];

echo '<b>--- DATA PEGAWAI ---</b><br>';
foreach ($daftar as $p) {
    echo 'ID ' . $p->getId() . ' | ' . $p->getNama() . ' | ' . $p->getJenis()
        . ' | Gaji: ' . rupiah($p->hitungGaji()) . '<br>';
}

echo '<br><b>--- PENGUJIAN VALIDASI ---</b><br>';

try {
    $x = new PegawaiTetap(3, 'Andi', -1000000);
} catch (InvalidArgumentException $e) {
    echo 'Gaji pokok negatif ditolak -> ' . $e->getMessage() . '<br>';
}

try {
    $x = new PegawaiKontrak(4, 'Rina', -35000, 160);
} catch (InvalidArgumentException $e) {
    echo 'Upah per jam negatif ditolak -> ' . $e->getMessage() . '<br>';
}

try {
    $x = new PegawaiKontrak(5, '   ', 35000, 160);
} catch (InvalidArgumentException $e) {
    echo 'Nama kosong ditolak (dicek parent) -> ' . $e->getMessage() . '<br>';
}
