<?php
declare(strict_types=1);

abstract class Pegawai {
    private int $id;
    private string $nama;

    public function __construct(int $id, string $nama) {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID pegawai harus lebih dari 0');
        }
        $nama = trim($nama);
        if ($nama === '') {
            throw new InvalidArgumentException('Nama pegawai wajib diisi');
        }
        $this->id = $id;
        $this->nama = $nama;
    }

    public function getId(): int { return $this->id; }
    public function getNama(): string { return $this->nama; }

    // protected: hanya bisa dipakai class ini dan child-nya
    protected function pastikanTidakNegatif(int $nilai, string $label): void {
        if ($nilai < 0) {
            throw new InvalidArgumentException($label . ' tidak boleh negatif');
        }
    }

    abstract public function hitungGaji(): int;
    abstract public function getJenis(): string;
}
