<?php
require_once 'config/koneksi.php';require_once 'config/auth.php';wajib_login();
$id=(int)$_SESSION['user_id'];
$awal=$_GET['awal']??date('Y-m-01');$akhir=$_GET['akhir']??date('Y-m-d');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$awal))$awal=date('Y-m-01');
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$akhir))$akhir=date('Y-m-d');
$s=$conn->prepare("SELECT r.tanggal,o.nama_olahraga,r.durasi_aktual,r.catatan FROM riwayat_olahraga r JOIN olahraga o ON o.id_olahraga=r.id_olahraga WHERE r.id_pengguna=? AND r.tanggal BETWEEN ? AND ? ORDER BY r.tanggal DESC");
$s->bind_param("iss",$id,$awal,$akhir);$s->execute();$rows=$s->get_result();$data=[];$sum=0;
while($r=$rows->fetch_assoc()){$sum+=(int)$r['durasi_aktual'];$data[]=$r;}
if(($_GET['export']??'')==='csv'){
 header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="laporan-fitdaily-'.$awal.'-'.$akhir.'.csv"');
 $out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,['Tanggal','Olahraga','Durasi (menit)','Catatan']);
 foreach($data as $r){$safe=[];foreach([$r['tanggal'],$r['nama_olahraga'],$r['durasi_aktual'],$r['catatan']] as $v){$v=(string)$v;if(preg_match('/^[=+\-@]/',$v))$v="'".$v;$safe[]=$v;}fputcsv($out,$safe);}
 fputcsv($out,[]);fputcsv($out,['Total durasi (menit)',$sum]);fclose($out);exit;
}
$pageTitle='Laporan';require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">REKAP AKTIVITAS</span><h1>Laporan Olahraga</h1><p>Lihat perkembangan aktivitas berdasarkan periode pilihanmu.</p></div><div class="d-flex gap-2 flex-wrap"><a class="btn btn-outline-success" href="?awal=<?= h($awal) ?>&akhir=<?= h($akhir) ?>&export=csv">↓ Ekspor CSV</a><button class="btn btn-primary" onclick="window.print()">Cetak / Simpan PDF</button></div></div>
<div class="panel"><div class="panel-head"><div><h2>Filter Periode</h2><p>Tentukan rentang laporan yang ingin dibuat.</p></div></div><form class="filter-form" method="get"><div><label class="form-label">Dari tanggal</label><input class="form-control" type="date" name="awal" value="<?= h($awal) ?>"></div><div><label class="form-label">Sampai tanggal</label><input class="form-control" type="date" name="akhir" value="<?= h($akhir) ?>"></div><button class="btn btn-primary align-self-end">Tampilkan</button><a class="btn btn-light align-self-end" href="laporan.php">Bulan ini</a></form></div>
<div class="row g-3 mb-4"><div class="col-12 col-md-4"><div class="stat-card"><div class="muted">Jumlah Aktivitas</div><div class="stat-number"><?= count($data) ?></div><small>Aktivitas dalam periode</small></div></div><div class="col-12 col-md-4"><div class="stat-card"><div class="muted">Total Durasi</div><div class="stat-number"><?= $sum ?><small class="fs-6"> menit</small></div><small>Akumulasi waktu olahraga</small></div></div><div class="col-12 col-md-4"><div class="stat-card"><div class="muted">Rata-rata</div><div class="stat-number"><?= count($data)?round($sum/count($data)):0 ?><small class="fs-6"> menit</small></div><small>Durasi per aktivitas</small></div></div></div>
<div class="panel"><div class="panel-head"><div><h2>Detail Laporan</h2><p>Periode <?= h(date('d M Y',strtotime($awal))) ?> sampai <?= h(date('d M Y',strtotime($akhir))) ?>.</p></div><span class="soft-pill"><?= count($data) ?> aktivitas</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Tanggal</th><th>Olahraga</th><th>Durasi (menit)</th><th>Catatan</th></tr></thead><tbody>
<?php foreach($data as $r): ?><tr><td><?= h(date('d M Y',strtotime($r['tanggal']))) ?></td><td class="fw-semibold"><?= h($r['nama_olahraga']) ?></td><td><?= (int)$r['durasi_aktual'] ?></td><td><?= h($r['catatan']) ?></td></tr><?php endforeach; ?>
<?php if(!$data): ?><tr><td colspan="4" class="text-center muted py-4">Belum ada aktivitas pada periode ini.</td></tr><?php endif; ?></tbody></table></div><div class="report-total">Total durasi olahraga: <strong><?= $sum ?> menit</strong></div></div>
<?php require 'includes/footer.php'; ?>
