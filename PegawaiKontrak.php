<?php
declare(strict_types=1);
require_once __DIR__ . '/Pegawai.php';

final class PegawaiKontrak extends Pegawai {
    private int $upahPerJam;
    private int $jamKerja;

    public function __construct(int $id, string $nama, int $upahPerJam, int $jamKerja) {
        parent::__construct($id, $nama);
        $this->pastikanTidakNegatif($upahPerJam, 'Upah per jam');
        $this->pastikanTidakNegatif($jamKerja, 'Jam kerja');
        $this->upahPerJam = $upahPerJam;
        $this->jamKerja = $jamKerja;
    }

    public function getUpahPerJam(): int { return $this->upahPerJam; }
    public function getJamKerja(): int { return $this->jamKerja; }

    // overriding: gaji kontrak = upah per jam x jam kerja
    public function hitungGaji(): int { return $this->upahPerJam * $this->jamKerja; }
    public function getJenis(): string { return 'Kontrak'; }
}
