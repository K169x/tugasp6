<?php
declare(strict_types=1);
require_once __DIR__ . '/Pegawai.php';

final class PegawaiTetap extends Pegawai {
    private int $gajiPokok;

    public function __construct(int $id, string $nama, int $gajiPokok) {
        parent::__construct($id, $nama);
        $this->pastikanTidakNegatif($gajiPokok, 'Gaji pokok');
        $this->gajiPokok = $gajiPokok;
    }

    public function getGajiPokok(): int { return $this->gajiPokok; }

    // overriding: gaji tetap = gaji pokok
    public function hitungGaji(): int { return $this->gajiPokok; }
    public function getJenis(): string { return 'Tetap'; }
}
