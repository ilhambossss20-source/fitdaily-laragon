<?php
require_once 'config/koneksi.php'; require_once 'config/auth.php'; require_once 'config/modules.php'; wajib_login();
$key=$_GET['modul']??'jadwal';
if(!isset($modules[$key])){http_response_code(404);exit('Modul tidak ditemukan.');}
$m=$modules[$key]; $table=$m['table']; $pk=$m['pk']; $fields=$m['fields']; $msg='';
function get_options($conn,$f){
 $sql="SELECT `".$f['refpk']."` AS id, `".$f['refname']."` AS nama FROM `".$f['ref']."` ORDER BY nama";
 return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 cek_csrf(); $aksi=$_POST['aksi']??'';
 if($aksi==='hapus'){
  $id=(int)$_POST['id']; $s=$conn->prepare("DELETE FROM `$table` WHERE `$pk`=?");$s->bind_param("i",$id);$s->execute();
  header("Location: kelola.php?modul=$key&pesan=hapus");exit;
 }
 if(in_array($aksi,['simpan','update'],true)){
  $vals=[];$types='';$names=[];
  foreach($fields as $col=>$f){
   $v=trim($_POST[$col]??'');
   if($f['type']==='datetime-local' && $v!=='') $v=str_replace('T',' ',$v).(strlen($v)===16?':00':'');
   if($col==='password' && $v==='') { if($aksi==='update') continue; }
   if($col==='password' && $v!=='') $v=password_hash($v,PASSWORD_DEFAULT);
   $vals[$col]=$v; $names[]="`$col`"; $types.='s';
  }
  if($aksi==='simpan'){
   $sql="INSERT INTO `$table` (".implode(',',$names).") VALUES (".implode(',',array_fill(0,count($names),'?')).")";
   $s=$conn->prepare($sql);$s->bind_param($types,...array_values($vals));$s->execute();
  }else{
   $id=(int)$_POST['id'];$sets=[];
   foreach(array_keys($vals) as $c)$sets[]="`$c`=?";
   $sql="UPDATE `$table` SET ".implode(',',$sets)." WHERE `$pk`=?";
   $types.='i';$vals[]=$id;$s=$conn->prepare($sql);$s->bind_param($types,...array_values($vals));$s->execute();
  }
  header("Location: kelola.php?modul=$key&pesan=simpan");exit;
 }
}
$edit=null;
if(($_GET['aksi']??'')==='edit'){
 $id=(int)($_GET['id']??0);$s=$conn->prepare("SELECT * FROM `$table` WHERE `$pk`=?");$s->bind_param("i",$id);$s->execute();$edit=$s->get_result()->fetch_assoc();
}
$rows=$conn->query("SELECT * FROM `$table` ORDER BY `$pk` DESC");
$pageTitle=$m['title'];require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">MANAJEMEN DATA</span><h1><?= h($m['title']) ?></h1><p>Tambah, lihat, ubah, dan hapus data <?= strtolower(h($m['title'])) ?>.</p></div>
<a class="btn btn-primary" href="kelola.php?modul=<?= h($key) ?>&aksi=tambah">+ Tambah Data</a></div>
<?php if(isset($_GET['pesan'])): ?><div class="alert alert-success">Data berhasil <?= $_GET['pesan']==='hapus'?'dihapus':'disimpan' ?>.</div><?php endif; ?>
<?php if(isset($_GET['aksi']) && in_array($_GET['aksi'],['tambah','edit'],true)): ?>
<div class="panel form-panel"><div class="panel-head"><div><h2><?= $edit?'Edit':'Tambah' ?> <?= h($m['title']) ?></h2><p>Lengkapi informasi berikut.</p></div></div>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="aksi" value="<?= $edit?'update':'simpan' ?>"><?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit[$pk] ?>"><?php endif; ?>
<div class="row g-3">
<?php foreach($fields as $col=>$f): $val=$edit[$col]??''; if($f['type']==='password' && $edit) $val=''; ?>
<div class="col-12 col-md-6"><label class="form-label"><?= h($f['label']) ?><?= $f['type']==='password' && $edit?' (kosongkan jika tidak diubah)':'' ?></label>
<?php if($f['type']==='textarea'): ?><textarea class="form-control" name="<?= h($col) ?>" rows="3"><?= h($val) ?></textarea>
<?php elseif($f['type']==='select'): ?><select class="form-select" name="<?= h($col) ?>" required><option value="">Pilih <?= h($f['label']) ?></option><?php foreach(get_options($conn,$f) as $op): ?><option value="<?= (int)$op['id'] ?>" <?= (string)$val===(string)$op['id']?'selected':'' ?>><?= h($op['nama']) ?></option><?php endforeach; ?></select>
<?php elseif($f['type']==='select_static'): ?><select class="form-select" name="<?= h($col) ?>"><?php foreach($f['options'] as $op): ?><option <?= $val===$op?'selected':'' ?>><?= h($op) ?></option><?php endforeach; ?></select>
<?php else: ?><input class="form-control" type="<?= h($f['type']) ?>" name="<?= h($col) ?>" value="<?= $f['type']==='datetime-local' && $val? h(date('Y-m-d\TH:i',strtotime($val))):h($val) ?>" <?= $col==='password' && $edit?'':'required' ?> <?= $f['type']==='number'?'min="1"':'' ?>><?php endif; ?></div>
<?php endforeach; ?>
</div><div class="d-flex gap-2 mt-4"><button class="btn btn-primary">Simpan</button><a class="btn btn-light" href="kelola.php?modul=<?= h($key) ?>">Batal</a></div></form></div>
<?php endif; ?>
<div class="panel"><div class="panel-head"><div><h2>Daftar <?= h($m['title']) ?></h2><p>Total <?= $rows->num_rows ?> data.</p></div><input id="tableSearch" class="form-control search-box" placeholder="Cari data..."></div>
<div class="table-responsive"><table class="table align-middle" id="dataTable"><thead><tr><?php foreach($fields as $f): ?><th><?= h($f['label']) ?></th><?php endforeach; ?><th>Aksi</th></tr></thead><tbody>
<?php while($r=$rows->fetch_assoc()): ?><tr><?php foreach($fields as $col=>$f): ?><td><?php
$v=$r[$col]??'';
if($f['type']==='select' && $v!==''){ $s=$conn->prepare("SELECT `".$f['refname']."` FROM `".$f['ref']."` WHERE `".$f['refpk']."`=?");$s->bind_param("i",$v);$s->execute();$v=$s->get_result()->fetch_row()[0]??$v; }
echo h($col==='password'?'••••••••':$v);
?></td><?php endforeach; ?><td class="text-nowrap"><a class="btn btn-sm btn-outline-success" href="kelola.php?modul=<?= h($key) ?>&aksi=edit&id=<?= (int)$r[$pk] ?>">Edit</a>
<form method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int)$r[$pk] ?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr><?php endwhile; ?>
<?php if($rows->num_rows===0): ?><tr><td colspan="<?= count($fields)+1 ?>" class="text-center muted py-4">Belum ada data.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
