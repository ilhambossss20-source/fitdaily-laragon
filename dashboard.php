<?php
require_once 'config/koneksi.php'; require_once 'config/auth.php'; wajib_login();
$id=(int)$_SESSION['user_id'];
function angka($conn,$sql,$id){$s=$conn->prepare($sql);$s->bind_param("i",$id);$s->execute();return (int)($s->get_result()->fetch_row()[0]??0);}
$total=angka($conn,"SELECT COUNT(*) FROM jadwal_olahraga WHERE id_pengguna=?",$id);
$selesai=angka($conn,"SELECT COUNT(*) FROM jadwal_olahraga WHERE id_pengguna=? AND status='Selesai'",$id);
$menit=angka($conn,"SELECT COALESCE(SUM(durasi_aktual),0) FROM riwayat_olahraga WHERE id_pengguna=?",$id);
$aktif=0;
$s=$conn->prepare("SELECT COUNT(*) FROM pengingat p JOIN jadwal_olahraga j ON j.id_jadwal=p.id_jadwal WHERE j.id_pengguna=? AND p.status='Aktif'");
$s->bind_param("i",$id);$s->execute();$aktif=(int)$s->get_result()->fetch_row()[0];

$s=$conn->prepare("SELECT j.tanggal,j.jam_mulai,j.jam_selesai,j.status,o.nama_olahraga FROM jadwal_olahraga j JOIN olahraga o ON o.id_olahraga=j.id_olahraga WHERE j.id_pengguna=? AND j.tanggal>=CURDATE() AND j.status='Terjadwal' ORDER BY j.tanggal,j.jam_mulai LIMIT 6");
$s->bind_param("i",$id);$s->execute();$jadwal=$s->get_result();

$labels=[];$values=[];$map=[];
for($i=6;$i>=0;$i--){$d=date('Y-m-d',strtotime("-$i days"));$labels[]=date('D',strtotime($d));$map[$d]=0;}
$s=$conn->prepare("SELECT tanggal,SUM(durasi_aktual) total FROM riwayat_olahraga WHERE id_pengguna=? AND tanggal>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY tanggal");
$s->bind_param("i",$id);$s->execute();$rr=$s->get_result();
while($r=$rr->fetch_assoc()){if(isset($map[$r['tanggal']]))$map[$r['tanggal']]=(int)$r['total'];}
$values=array_values($map);
$pageTitle='Dashboard'; require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">RINGKASAN AKTIVITAS</span><h1>Dashboard</h1><p>Selamat beraktivitas, <?= h($_SESSION['nama']??'Pengguna') ?>. Yuk jaga konsistensi olahragamu.</p></div><a class="btn btn-primary" href="kelola.php?modul=jadwal&aksi=tambah">＋ Tambah Jadwal</a></div>
<div class="row g-3 mb-4">
 <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon">▣</span><div class="muted">Total Jadwal</div><div class="stat-number"><?= $total ?></div><small>Seluruh jadwal olahraga</small></div></div>
 <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon">✓</span><div class="muted">Jadwal Selesai</div><div class="stat-number"><?= $selesai ?></div><small>Aktivitas yang dituntaskan</small></div></div>
 <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon">◷</span><div class="muted">Total Durasi</div><div class="stat-number"><?= $menit ?><small class="fs-6"> menit</small></div><small>Akumulasi riwayat olahraga</small></div></div>
 <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card"><span class="stat-icon">♧</span><div class="muted">Pengingat Aktif</div><div class="stat-number"><?= $aktif ?></div><small>Pengingat yang diaktifkan</small></div></div>
</div>
<div class="row g-3">
 <div class="col-12 col-xl-7"><div class="panel h-100"><div class="panel-head"><div><h2>Aktivitas 7 Hari Terakhir</h2><p>Total durasi olahraga per hari dalam menit.</p></div><span class="soft-pill">Mingguan</span></div><div class="chart-wrap"><canvas id="weeklyChart"></canvas></div></div></div>
 <div class="col-12 col-xl-5"><div class="panel h-100"><div class="panel-head"><div><h2>Akses Cepat</h2><p>Kelola aktivitasmu dengan mudah.</p></div></div><div class="quick-grid">
 <a href="kalender.php" class="quick-link"><span>▦</span><b>Kalender</b><small>Lihat jadwal bulanan</small></a>
 <a href="pengingat.php" class="quick-link"><span>♧</span><b>Pengingat</b><small>Atur notifikasi</small></a>
 <a href="riwayat.php" class="quick-link"><span>◷</span><b>Riwayat</b><small>Aktivitas yang tercatat</small></a>
 <a href="laporan.php" class="quick-link"><span>▤</span><b>Laporan</b><small>Rekap dan cetak</small></a>
 </div></div></div>
</div>
<div class="panel mt-4"><div class="panel-head"><div><h2>Jadwal Mendatang</h2><p>Enam jadwal terdekat yang masih terencana.</p></div><a href="kalender.php">Buka kalender →</a></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Olahraga</th><th>Tanggal</th><th>Waktu</th><th>Status</th></tr></thead><tbody>
<?php if($jadwal->num_rows): while($r=$jadwal->fetch_assoc()): ?><tr><td class="fw-semibold"><?= h($r['nama_olahraga']) ?></td><td><?= h(date('d M Y',strtotime($r['tanggal']))) ?></td><td><?= h(substr($r['jam_mulai'],0,5)) ?> - <?= h(substr($r['jam_selesai'],0,5)) ?></td><td><span class="status"><?= h($r['status']) ?></span></td></tr><?php endwhile; else: ?><tr><td colspan="4" class="text-center muted py-4">Belum ada jadwal mendatang. Tambahkan jadwal pertamamu.</td></tr><?php endif; ?>
</tbody></table></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartEl=document.getElementById('weeklyChart');
if(chartEl){new Chart(chartEl,{type:'bar',data:{labels:<?= json_encode($labels) ?>,datasets:[{label:'Durasi (menit)',data:<?= json_encode($values) ?>,backgroundColor:'#728E6D',borderRadius:8,maxBarThickness:34}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}}}});}
</script>
<?php require 'includes/footer.php'; ?>
