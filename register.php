<?php
require_once 'config/koneksi.php'; require_once 'config/auth.php';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 cek_csrf(); $nama=trim($_POST['nama_pengguna']??''); $username=trim($_POST['username']??'');
 $email=trim($_POST['email']??''); $password=$_POST['password']??'';
 if(strlen($password)<8) $error='Password minimal 8 karakter.';
 else {
  $cek=$conn->prepare("SELECT id_pengguna FROM pengguna WHERE username=? OR email=?");
  $cek->bind_param("ss",$username,$email); $cek->execute();
  if($cek->get_result()->num_rows) $error='Username atau email sudah digunakan.';
  else {
   $hash=password_hash($password,PASSWORD_DEFAULT);
   $s=$conn->prepare("INSERT INTO pengguna(nama_pengguna,username,password,email,tanggal_daftar) VALUES(?,?,?,?,NOW())");
   $s->bind_param("ssss",$nama,$username,$hash,$email); $s->execute();
   header('Location: login.php?daftar=berhasil'); exit;
  }
 }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Daftar FITDAILY</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head>
<body class="auth-bg"><div class="auth-card"><div class="brand justify-content-center mb-4"><span class="brand-mark">F</span><span>FITDAILY<small>Buat akun baru</small></span></div><h1 class="h3 fw-bold text-center">Registrasi</h1>
<?php if($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<label class="form-label">Nama lengkap</label><input class="form-control mb-3" name="nama_pengguna" required>
<label class="form-label">Username</label><input class="form-control mb-3" name="username" required>
<label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required>
<label class="form-label">Password (minimal 8 karakter)</label><input class="form-control mb-4" type="password" name="password" minlength="8" required>
<button class="btn btn-primary w-100">Buat Akun</button></form><p class="text-center small mt-3">Sudah punya akun? <a href="login.php">Login</a></p></div></body></html>
