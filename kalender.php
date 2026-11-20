<?php
require_once 'config/koneksi.php';require_once 'config/auth.php';wajib_login();
$id=(int)$_SESSION['user_id'];
$month=$_GET['bulan']??date('Y-m');
if(!preg_match('/^\d{4}-\d{2}$/',$month))$month=date('Y-m');
$first=DateTime::createFromFormat('!Y-m-d',$month.'-01');
if(!$first)$first=new DateTime('first day of this month');
$start=$first->format('Y-m-01');$end=(clone $first)->modify('last day of this month')->format('Y-m-d');
$prev=(clone $first)->modify('-1 month')->format('Y-m');$next=(clone $first)->modify('+1 month')->format('Y-m');
$s=$conn->prepare("SELECT j.id_jadwal,j.tanggal,j.jam_mulai,j.jam_selesai,j.status,o.nama_olahraga FROM jadwal_olahraga j JOIN olahraga o ON o.id_olahraga=j.id_olahraga WHERE j.id_pengguna=? AND j.tanggal BETWEEN ? AND ? ORDER BY j.tanggal,j.jam_mulai");
$s->bind_param("iss",$id,$start,$end);$s->execute();$res=$s->get_result();$events=[];
while($r=$res->fetch_assoc())$events[$r['tanggal']][]=$r;
$offset=(int)$first->format('N')-1;$days=(int)$first->format('t');$cells=(int)(ceil(($offset+$days)/7)*7);
$pageTitle='Kalender';require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">PERENCANAAN AKTIVITAS</span><h1>Kalender Olahraga</h1><p>Lihat dan pantau jadwal olahraga berdasarkan tanggal.</p></div><a class="btn btn-primary" href="kelola.php?modul=jadwal&aksi=tambah">＋ Tambah Jadwal</a></div>
<div class="panel calendar-panel">
 <div class="calendar-toolbar"><a class="btn btn-light" href="?bulan=<?= h($prev) ?>">← Sebelumnya</a><h2><?= h($first->format('F Y')) ?></h2><a class="btn btn-light" href="?bulan=<?= h($next) ?>">Berikutnya →</a></div>
 <div class="calendar-grid calendar-weekdays"><?php foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $day): ?><div><?= $day ?></div><?php endforeach; ?></div>
 <div class="calendar-grid">
 <?php for($i=0;$i<$cells;$i++): $dayNum=$i-$offset+1;$valid=$dayNum>=1&&$dayNum<=$days;$date=$valid?$first->format('Y-m-').str_pad((string)$dayNum,2,'0',STR_PAD_LEFT):'';$isToday=$date===date('Y-m-d'); ?>
 <div class="calendar-day <?= !$valid?'is-empty':'' ?> <?= $isToday?'is-today':'' ?>">
 <?php if($valid): ?><div class="calendar-date"><?= $dayNum ?> <?= $isToday?'<span>Hari ini</span>':'' ?></div>
 <?php foreach(($events[$date]??[]) as $ev): ?><a class="calendar-event <?= $ev['status']==='Selesai'?'event-done':($ev['status']==='Dibatalkan'?'event-cancel':'') ?>" href="kelola.php?modul=jadwal&aksi=edit&id=<?= (int)$ev['id_jadwal'] ?>"><b><?= h(substr($ev['jam_mulai'],0,5)) ?></b> <?= h($ev['nama_olahraga']) ?><small><?= h($ev['status']) ?></small></a><?php endforeach; ?>
 <?php endif; ?></div>
 <?php endfor; ?>
 </div>
 <div class="calendar-legend"><span><i class="legend-dot"></i> Terjadwal</span><span><i class="legend-dot done"></i> Selesai</span><span><i class="legend-dot cancel"></i> Dibatalkan</span></div>
</div>
<?php require 'includes/footer.php'; ?>
