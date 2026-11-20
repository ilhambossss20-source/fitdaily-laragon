<?php
require_once 'config/koneksi.php';require_once 'config/auth.php';wajib_login();
$id=(int)$_SESSION['user_id'];$awal=$_GET['awal']??date('Y-m-01');$akhir=$_GET['akhir']??date('Y-m-d');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$awal))$awal=date('Y-m-01');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$akhir))$akhir=date('Y-m-d');
$s=$conn->prepare("SELECT r.id_riwayat,r.tanggal,r.durasi_aktual,r.catatan,o.nama_olahraga FROM riwayat_olahraga r JOIN olahraga o ON o.id_olahraga=r.id_olahraga WHERE r.id_pengguna=? AND r.tanggal BETWEEN ? AND ? ORDER BY r.tanggal DESC,r.id_riwayat DESC");
$s->bind_param("iss",$id,$awal,$akhir);$s->execute();$rows=$s->get_result();$data=[];$total=0;
while($r=$rows->fetch_assoc()){$total+=(int)$r['durasi_aktual'];$data[]=$r;}
$pageTitle='Riwayat Olahraga';require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CATATAN PERKEMBANGAN</span><h1>Riwayat Olahraga</h1><p>Semua aktivitas yang sudah kamu catat.</p></div><a class="btn btn-primary" href="kelola.php?modul=riwayat&aksi=tambah">＋ Catat Aktivitas</a></div>
<div class="row g-3 mb-4"><div class="col-12 col-md-4"><div class="stat-card"><div class="muted">Jumlah Aktivitas</div><div class="stat-number"><?= count($data) ?></div><small>Aktivitas pada rentang tanggal</small></div></div><div class="col-12 col-md-4"><div class="stat-card"><div class="muted">Total Durasi</div><div class="stat-number"><?= $total ?><small class="fs-6"> menit</small></div><small>Akumulasi durasi olahraga</small></div></div><div class="col-12 col-md-4"><div class="stat-card"><div class="muted">Rata-rata Durasi</div><div class="stat-number"><?= count($data)?round($total/count($data)):0 ?><small class="fs-6"> menit</small></div><small>Per aktivitas tercatat</small></div></div></div>
<div class="panel"><div class="panel-head"><div><h2>Filter Riwayat</h2><p>Pilih periode aktivitas yang ingin dilihat.</p></div></div><form class="filter-form" method="get"><div><label class="form-label">Dari tanggal</label><input class="form-control" type="date" name="awal" value="<?= h($awal) ?>"></div><div><label class="form-label">Sampai tanggal</label><input class="form-control" type="date" name="akhir" value="<?= h($akhir) ?>"></div><button class="btn btn-primary align-self-end">Terapkan Filter</button><a class="btn btn-light align-self-end" href="riwayat.php">Reset</a></form></div>
<div class="panel"><div class="panel-head"><div><h2>Aktivitas Tercatat</h2><p><?= count($data) ?> aktivitas ditemukan.</p></div><a href="laporan.php">Lihat laporan →</a></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Tanggal</th><th>Olahraga</th><th>Durasi</th><th>Catatan</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($data as $r): ?><tr><td><?= h(date('d M Y',strtotime($r['tanggal']))) ?></td><td class="fw-semibold"><?= h($r['nama_olahraga']) ?></td><td><?= (int)$r['durasi_aktual'] ?> menit</td><td><?= h($r['catatan']) ?></td><td><a class="btn btn-sm btn-outline-success" href="kelola.php?modul=riwayat&aksi=edit&id=<?= (int)$r['id_riwayat'] ?>">Edit</a></td></tr><?php endforeach; ?>
<?php if(!$data): ?><tr><td colspan="5" class="text-center muted py-4">Belum ada riwayat pada periode ini.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
