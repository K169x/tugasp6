<?php
require_once __DIR__ . '/Mahasiswa.php';
require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';

class DataMahasiswa {
    /** @var array<string, Mahasiswa> */
    private array $data = [];

    public function __construct() {
        if (isset($_SESSION['data_mhs_tugas6'])) {
            $this->data = $_SESSION['data_mhs_tugas6'];
        }
    }

    private function simpanKeSession(): void {
        $_SESSION['data_mhs_tugas6'] = $this->data;
    }

    // Parameter bertipe Mahasiswa: menerima MahasiswaReguler maupun MahasiswaBeasiswa
    public function simpan(Mahasiswa $m, bool $isEdit = false): void {
        $nim = $m->getNim();

        if ($isEdit) {
            if (!isset($this->data[$nim])) {
                throw new InvalidArgumentException("Data dengan NIM '{$nim}' tidak ditemukan.");
            }
        } elseif (isset($this->data[$nim])) {
            throw new InvalidArgumentException("NIM '{$nim}' sudah terdaftar!");
        }

        $this->data[$nim] = $m;
        $this->simpanKeSession();
    }

    public function hapus(string $nim): void {
        if (isset($this->data[$nim])) {
            unset($this->data[$nim]);
            $this->simpanKeSession();
        }
    }

    public function cari(string $nim): ?Mahasiswa {
        return $this->data[$nim] ?? null;
    }

    // Pencarian berdasarkan kata kunci (NIM, nama, prodi, jenis)
    public function cariKeyword(string $kata): array {
        $kata = mb_strtolower(trim($kata));
        if ($kata === '') return $this->data;

        return array_filter($this->data, function (Mahasiswa $m) use ($kata): bool {
            return str_contains(mb_strtolower($m->getNim()), $kata)
                || str_contains(mb_strtolower($m->getNama()), $kata)
                || str_contains(mb_strtolower($m->getProdi()), $kata)
                || str_contains(mb_strtolower($m->getJenis()), $kata);
        });
    }

    public function getAll(): array {
        return $this->data;
    }
}
