<?php
require_once 'config/koneksi.php';require_once 'config/auth.php';wajib_login();
$id=(int)$_SESSION['user_id'];$error='';$success='';
$s=$conn->prepare("SELECT nama_pengguna,username,email,password FROM pengguna WHERE id_pengguna=? LIMIT 1");$s->bind_param("i",$id);$s->execute();$user=$s->get_result()->fetch_assoc();
if($_SERVER['REQUEST_METHOD']==='POST'){
 cek_csrf();$aksi=$_POST['aksi']??'';
 if($aksi==='profil'){
  $nama=trim($_POST['nama_pengguna']??'');$email=trim($_POST['email']??'');
  if($nama===''||!filter_var($email,FILTER_VALIDATE_EMAIL))$error='Nama dan email harus diisi dengan benar.';
  else{$cek=$conn->prepare("SELECT id_pengguna FROM pengguna WHERE email=? AND id_pengguna<>?");$cek->bind_param("si",$email,$id);$cek->execute();
   if($cek->get_result()->num_rows)$error='Email tersebut sudah digunakan akun lain.';
   else{$up=$conn->prepare("UPDATE pengguna SET nama_pengguna=?,email=? WHERE id_pengguna=?");$up->bind_param("ssi",$nama,$email,$id);$up->execute();$_SESSION['nama']=$nama;$user['nama_pengguna']=$nama;$user['email']=$email;$success='Profil berhasil diperbarui.';}
  }
 }elseif($aksi==='password'){
  $lama=$_POST['password_lama']??'';$baru=$_POST['password_baru']??'';$konfirmasi=$_POST['konfirmasi_password']??'';
  $valid=password_verify($lama,$user['password'])||hash_equals((string)$user['password'],$lama);
  if(!$valid)$error='Password saat ini tidak benar.';
  elseif(strlen($baru)<8)$error='Password baru minimal 8 karakter.';
  elseif($baru!==$konfirmasi)$error='Konfirmasi password tidak sama.';
  else{$hash=password_hash($baru,PASSWORD_DEFAULT);$up=$conn->prepare("UPDATE pengguna SET password=? WHERE id_pengguna=?");$up->bind_param("si",$hash,$id);$up->execute();$user['password']=$hash;$success='Password berhasil diganti.';}
 }
}
$pageTitle='Pengaturan';require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">PREFERENSI AKUN</span><h1>Pengaturan</h1><p>Atur informasi akun dan tampilan FITDAILY.</p></div></div>
<?php if($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?><?php if($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>
<div class="row g-4"><div class="col-12 col-xl-7"><div class="panel"><div class="panel-head"><div><h2>Profil Pengguna</h2><p>Perbarui informasi akunmu.</p></div></div><form method="post" class="settings-form"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="aksi" value="profil"><label class="form-label">Nama lengkap</label><input class="form-control mb-3" name="nama_pengguna" value="<?= h($user['nama_pengguna']) ?>" required><label class="form-label">Username</label><input class="form-control mb-3" value="<?= h($user['username']) ?>" disabled><label class="form-label">Email</label><input class="form-control mb-4" type="email" name="email" value="<?= h($user['email']) ?>" required><button class="btn btn-primary">Simpan Profil</button></form></div>
<div class="panel"><div class="panel-head"><div><h2>Keamanan Akun</h2><p>Gunakan password yang kuat dan tidak mudah ditebak.</p></div></div><form method="post" class="settings-form"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="aksi" value="password"><label class="form-label">Password saat ini</label><input class="form-control mb-3" type="password" name="password_lama" required><label class="form-label">Password baru</label><input class="form-control mb-3" type="password" name="password_baru" minlength="8" required><label class="form-label">Konfirmasi password baru</label><input class="form-control mb-4" type="password" name="konfirmasi_password" minlength="8" required><button class="btn btn-primary">Ganti Password</button></form></div></div>
<div class="col-12 col-xl-5"><div class="panel"><div class="panel-head"><div><h2>Tampilan Website</h2><p>Sesuaikan tampilan dengan kenyamananmu.</p></div></div><div class="settings-form"><div class="theme-option"><div><b>Mode gelap</b><small>Ubah tampilan FITDAILY menjadi gelap.</small></div><label class="switch"><input type="checkbox" id="themeSwitch"><span></span></label></div><div class="theme-preview"><span class="preview-dot"></span><span>FITDAILY</span><small>Atur Jadwalmu, Jaga Kesehatanmu.</small></div><p class="muted small mt-3 mb-0">Preferensi tema disimpan pada browser yang sedang digunakan.</p></div></div><div class="panel p-4"><span class="eyebrow">TIPS KEAMANAN</span><h2 class="h5 fw-bold mt-2">Jaga akunmu</h2><p class="muted mb-0">Jangan membagikan password kepada orang lain dan selalu keluar dari akun setelah menggunakan perangkat bersama.</p></div></div></div>
<?php require 'includes/footer.php'; ?>
