<?php
require_once 'config/koneksi.php';require_once 'config/auth.php';wajib_login();
$id=(int)$_SESSION['user_id'];
$s=$conn->prepare("SELECT p.id_pengingat,p.waktu_pengingat,p.pesan,p.status,j.tanggal,j.jam_mulai,o.nama_olahraga FROM pengingat p JOIN jadwal_olahraga j ON j.id_jadwal=p.id_jadwal JOIN olahraga o ON o.id_olahraga=j.id_olahraga WHERE j.id_pengguna=? ORDER BY p.waktu_pengingat ASC");
$s->bind_param("i",$id);$s->execute();$rows=$s->get_result();$items=[];
while($r=$rows->fetch_assoc())$items[]=$r;
$active=array_values(array_filter($items,fn($r)=>$r['status']==='Aktif'));
$pageTitle='Pengingat';require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">JANGAN LEWATKAN JADWAL</span><h1>Pengingat Olahraga</h1><p>Kelola pengingat agar jadwal olahragamu tidak terlewat.</p></div><a class="btn btn-primary" href="kelola.php?modul=pengingat&aksi=tambah">＋ Buat Pengingat</a></div>
<div class="reminder-hero"><div><span class="eyebrow">PENGINGAT AKTIF</span><div class="reminder-count"><?= count($active) ?> <small>pengingat</small></div><p>Izinkan notifikasi browser agar FITDAILY dapat memberi pemberitahuan saat halaman ini terbuka.</p></div><button class="btn btn-light" id="enableNotifications">Aktifkan Notifikasi Browser</button></div>
<div class="alert alert-info mt-3">Catatan: notifikasi browser bekerja saat FITDAILY sedang dibuka. Untuk pengingat otomatis saat browser tertutup, diperlukan layanan penjadwalan/server tambahan.</div>
<div class="panel mt-4"><div class="panel-head"><div><h2>Daftar Pengingat</h2><p>Pengingat yang terhubung dengan jadwal olahragamu.</p></div><span class="soft-pill"><?= count($items) ?> data</span></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Waktu Pengingat</th><th>Olahraga</th><th>Jadwal</th><th>Pesan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $r): ?><tr><td><?= h(date('d M Y H:i',strtotime($r['waktu_pengingat']))) ?></td><td class="fw-semibold"><?= h($r['nama_olahraga']) ?></td><td><?= h(date('d M Y',strtotime($r['tanggal']))) ?> · <?= h(substr($r['jam_mulai'],0,5)) ?></td><td><?= h($r['pesan']) ?></td><td><span class="status <?= $r['status']==='Aktif'?'':'status-muted' ?>"><?= h($r['status']) ?></span></td><td><a class="btn btn-sm btn-outline-success" href="kelola.php?modul=pengingat&aksi=edit&id=<?= (int)$r['id_pengingat'] ?>">Edit</a></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td colspan="6" class="text-center muted py-4">Belum ada pengingat. Buat pengingat pertamamu.</td></tr><?php endif; ?></tbody></table></div></div>
<script>
const fitReminders=<?= json_encode(array_map(fn($r)=>['id'=>(int)$r['id_pengingat'],'at'=>date('c',strtotime($r['waktu_pengingat'])),'title'=>$r['nama_olahraga'],'message'=>$r['pesan'],'status'=>$r['status']],$active),JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
const notifyBtn=document.getElementById('enableNotifications');
if(notifyBtn){notifyBtn.addEventListener('click',async()=>{if(!('Notification' in window)){alert('Browser ini belum mendukung notifikasi.');return;}const permission=await Notification.requestPermission();alert(permission==='granted'?'Notifikasi berhasil diizinkan. Biarkan halaman ini terbuka agar pengingat dapat diperiksa.':'Izin notifikasi belum diberikan.');});}
function checkFitReminders(){if(!('Notification' in window)||Notification.permission!=='granted')return;const now=Date.now();fitReminders.forEach(r=>{const when=new Date(r.at).getTime();const key='fitdaily-reminder-'+r.id;if(when<=now&&when>now-24*60*60*1000&&!sessionStorage.getItem(key)){new Notification('Waktunya olahraga: '+r.title,{body:r.message||'Jadwal olahragamu sudah tiba.'});sessionStorage.setItem(key,'1');}});}
checkFitReminders();setInterval(checkFitReminders,60000);
</script>
<?php require 'includes/footer.php'; ?>
