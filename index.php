<?php
require_once __DIR__ . '/Mahasiswa.php';
require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';
require_once __DIR__ . '/DataMahasiswa.php'; // class harus dimuat sebelum session_start
session_start();

function aman(string|int $data): string {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

function rupiah(int $angka): string {
    return 'Rp' . number_format($angka, 0, ',', '.');
}

// Membuat object child sesuai jenis yang dipilih di form
function buatMahasiswa(array $in, string $nim): Mahasiswa {
    $nama     = trim((string) ($in['nama'] ?? ''));
    $fakultas = trim((string) ($in['fakultas'] ?? ''));
    $prodi    = trim((string) ($in['prodi'] ?? ''));
    $asal     = trim((string) ($in['asal_sekolah'] ?? ''));
    $semester = (int) ($in['semester'] ?? 0);
    $hobi     = trim((string) ($in['hobi'] ?? ''));
    $agama    = trim((string) ($in['agama'] ?? ''));

    $ukt = filter_var(trim((string) ($in['ukt'] ?? '')), FILTER_VALIDATE_INT);
    if ($ukt === false) {
        throw new InvalidArgumentException('UKT harus berupa bilangan bulat');
    }

    $jenis = (string) ($in['jenis'] ?? '');
    if ($jenis === 'reguler') {
        return new MahasiswaReguler($nim, $nama, $fakultas, $prodi, $asal, $semester, $hobi, $agama, $ukt);
    }
    if ($jenis === 'beasiswa') {
        $potongan = filter_var(trim((string) ($in['potongan'] ?? '')), FILTER_VALIDATE_INT);
        if ($potongan === false) {
            throw new InvalidArgumentException('Potongan beasiswa harus berupa bilangan bulat');
        }
        return new MahasiswaBeasiswa($nim, $nama, $fakultas, $prodi, $asal, $semester, $hobi, $agama, $ukt, $potongan);
    }
    throw new InvalidArgumentException('Jenis mahasiswa tidak dikenal');
}

$repo     = new DataMahasiswa();
$error    = '';
$sukses   = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$editData = null;
$modeEdit = false;
$nimEdit  = '';
$keyword  = trim((string) ($_GET['q'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = (string) ($_POST['aksi'] ?? 'tambah');

    // Hapus memakai POST (bukan GET) karena mengubah data
    if ($aksi === 'hapus') {
        $repo->hapus(trim((string) ($_POST['nim'] ?? '')));
        $_SESSION['flash'] = 'Data berhasil dihapus!';
        header('Location: index.php');
        exit;
    }

    $isEdit = ($aksi === 'ubah');
    // Saat edit, NIM diambil dari nim_lama (bukan dari input yang bisa dimanipulasi)
    $nim = trim((string) ($isEdit ? ($_POST['nim_lama'] ?? '') : ($_POST['nim'] ?? '')));

    try {
        $m = buatMahasiswa($_POST, $nim);
        $repo->simpan($m, $isEdit);
        $_SESSION['flash'] = $isEdit ? 'Data berhasil diupdate!' : 'Data berhasil ditambah!';
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error    = $e->getMessage();
        $modeEdit = $isEdit;
        $nimEdit  = $nim;
    }
} elseif (isset($_GET['edit'])) {
    $editData = $repo->cari((string) $_GET['edit']);
    if ($editData) {
        $modeEdit = true;
        $nimEdit  = $editData->getNim();
    }
}

// Nilai form: dari POST jika ada error, dari object jika mode edit
$val = function (string $field, mixed $dariObjek = '') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return (string) ($_POST[$field] ?? '');
    }
    return (string) ($dariObjek ?? '');
};

$jenisAktif = $val('jenis', $editData ? strtolower($editData->getJenis()) : 'reguler');
$agamaAktif = $val('agama', $editData?->getAgama());
$potonganAwal = ($editData instanceof MahasiswaBeasiswa) ? $editData->getPotonganPersen() : '';

$semuaData = $repo->cariKeyword($keyword);
$total     = count($repo->getAll());
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Mahasiswa - Inheritance</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .card { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; font-size: 1.4em; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 500; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-family: inherit; }
        input[readonly] { background-color: #e9ecef; cursor: not-allowed; color: #6c757d; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        button, .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1em; text-decoration: none; text-align: center; }
        .btn-primary { background-color: #3498db; color: white; width: 100%; }
        .btn-primary:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; color: white; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-secondary { background-color: #95a5a6; color: white; width: 100%; display: inline-block; }
        .btn-sm { padding: 6px 10px; font-size: 0.85em; margin-right: 2px; width: auto; }
        .search { display: flex; gap: 8px; margin-bottom: 10px; }
        .search input { flex: 1; }
        .search button, .search a { width: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #2c3e50; }
        tr:hover { background-color: #f1f4f6; }
        .table-responsive { overflow-x: auto; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2><?= $modeEdit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>

            <?php if ($error): ?> <div class="alert alert-error"><?= aman($error) ?></div> <?php endif; ?>
            <?php if ($sukses): ?> <div class="alert alert-success"><?= aman($sukses) ?></div> <?php endif; ?>

            <form method="POST" action="index.php">
                <input type="hidden" name="aksi" value="<?= $modeEdit ? 'ubah' : 'tambah' ?>">
                <?php if ($modeEdit): ?>
                    <input type="hidden" name="nim_lama" value="<?= aman($nimEdit) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Jenis Mahasiswa</label>
                    <select name="jenis" id="jenis">
                        <option value="reguler" <?= $jenisAktif === 'reguler' ? 'selected' : '' ?>>Reguler</option>
                        <option value="beasiswa" <?= $jenisAktif === 'beasiswa' ? 'selected' : '' ?>>Beasiswa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>NIM</label>
                    <?php if ($modeEdit): ?>
                        <input type="text" name="nim" value="<?= aman($nimEdit) ?>" readonly>
                    <?php else: ?>
                        <input type="text" name="nim" value="<?= aman($val('nim')) ?>" placeholder="Masukkan NIM">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= aman($val('nama', $editData?->getNama())) ?>" placeholder="Masukkan Nama">
                </div>

                <div class="form-group">
                    <label>Fakultas</label>
                    <input type="text" name="fakultas" value="<?= aman($val('fakultas', $editData?->getFakultas())) ?>" placeholder="Fakultas">
                </div>

                <div class="form-group">
                    <label>Program Studi</label>
                    <input type="text" name="prodi" value="<?= aman($val('prodi', $editData?->getProdi())) ?>" placeholder="Program Studi">
                </div>

                <div class="form-group">
                    <label>Semester (1-14)</label>
                    <input type="number" name="semester" value="<?= aman($val('semester', $editData?->getSemester())) ?>" placeholder="1">
                </div>

                <div class="form-group">
                    <label>Asal Sekolah</label>
                    <input type="text" name="asal_sekolah" value="<?= aman($val('asal_sekolah', $editData?->getAsalSekolah())) ?>" placeholder="Nama SMA/SMK">
                </div>

                <div class="form-group">
                    <label>Hobi</label>
                    <input type="text" name="hobi" value="<?= aman($val('hobi', $editData?->getHobi())) ?>" placeholder="Contoh: Membaca, Coding">
                </div>

                <div class="form-group">
                    <label>Agama</label>
                    <select name="agama">
                        <option value="">-- Pilih Agama --</option>
                        <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm): ?>
                            <option value="<?= $agm ?>" <?= $agamaAktif === $agm ? 'selected' : '' ?>><?= $agm ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>UKT (Rp, minimal 0)</label>
                    <input type="number" name="ukt" value="<?= aman($val('ukt', $editData?->getUkt())) ?>" placeholder="5000000">
                </div>

                <div class="form-group" id="box-potongan">
                    <label>Potongan Beasiswa (0-100 %)</label>
                    <input type="number" name="potongan" value="<?= aman($val('potongan', $potonganAwal)) ?>" placeholder="60">
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary"><?= $modeEdit ? 'Update Data' : 'Simpan Data' ?></button>
                    <?php if ($modeEdit): ?>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Data Mahasiswa (<?= count($semuaData) ?> dari <?= $total ?>)</h2>

            <form method="GET" action="index.php" class="search">
                <input type="text" name="q" value="<?= aman($keyword) ?>" placeholder="Cari NIM / nama / prodi / jenis">
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if ($keyword !== ''): ?> <a href="index.php" class="btn btn-secondary">Reset</a> <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Smt</th>
                            <th>Jenis</th>
                            <th>UKT</th>
                            <th>Biaya Akhir</th>
                            <th style="min-width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($semuaData)): ?>
                            <tr><td colspan="8" style="text-align:center; color:#7f8c8d;">Data tidak ditemukan / belum ada data tersimpan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($semuaData as $m): ?>
                            <tr>
                                <td><strong><?= aman($m->getNim()) ?></strong></td>
                                <td><?= aman($m->getNama()) ?></td>
                                <td><?= aman($m->getProdi()) ?></td>
                                <td><?= aman($m->getSemester()) ?></td>
                                <td><?= aman($m->getJenis()) ?></td>
                                <td><?= aman(rupiah($m->getUkt())) ?></td>
                                <td><strong><?= aman(rupiah($m->hitungBiaya())) ?></strong></td>
                                <td>
                                    <a href="index.php?edit=<?= rawurlencode($m->getNim()) ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <form method="POST" action="index.php" style="display:inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Kolom potongan hanya tampil untuk jenis Beasiswa
        const jenis = document.getElementById('jenis');
        const boxPotongan = document.getElementById('box-potongan');
        function aturPotongan() {
            boxPotongan.style.display = (jenis.value === 'beasiswa') ? 'block' : 'none';
        }
        jenis.addEventListener('change', aturPotongan);
        aturPotongan();
    </script>
</body>
</html>
