<?php
declare(strict_types=1);

class Alamat {
    public function __construct(private string $jalan, private string $kota) {}
    public function getKota(): string { return $this->kota; }
    public function format(): string { return $this->jalan . ', ' . $this->kota; }
}

class KartuPerpustakaan {
    public function __construct(private string $nomor) {}
    public function getNomor(): string { return $this->nomor; }
}

// ===== DESAIN KELIRU: Mahasiswa dibuat turunan dari Alamat =====
class MahasiswaKeliru extends Alamat {
    public function __construct(private string $nama, string $jalan, string $kota) {
        parent::__construct($jalan, $kota);
    }
    public function getNama(): string { return $this->nama; }
}

// ===== PERBAIKAN: komposisi (has-a) =====
class MahasiswaKomposisi {
    public function __construct(
        private string $nama,
        private Alamat $alamat,
        private KartuPerpustakaan $kartu
    ) {}

    public function info(): string {
        return $this->nama . ' | Alamat: ' . $this->alamat->format()
            . ' | Kartu: ' . $this->kartu->getNomor();
    }
}

echo '<b>--- DESAIN KELIRU (extends) ---</b><br>';
$keliru = new MahasiswaKeliru('Andi', 'Jl. Veteran No. 10', 'Sukabumi');
echo $keliru->getNama() . ' adalah Alamat? -> ' . ($keliru instanceof Alamat ? 'ya' : 'tidak') . '<br>';
echo 'Mahasiswa bisa dipanggil getKota(): ' . $keliru->getKota() . ' (janggal)<br><br>';

echo '<b>--- PERBAIKAN (komposisi) ---</b><br>';
$benar = new MahasiswaKomposisi(
    'Andi',
    new Alamat('Jl. Veteran No. 10', 'Sukabumi'),
    new KartuPerpustakaan('LIB-001')
);
echo $benar->info() . '<br>';
