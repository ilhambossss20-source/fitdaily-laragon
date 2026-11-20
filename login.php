<?php
require_once 'config/koneksi.php';
require_once 'config/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 cek_csrf();
 $username = trim($_POST['username'] ?? '');
 $password = $_POST['password'] ?? '';
 $stmt = $conn->prepare("SELECT id_pengguna,nama_pengguna,username,password FROM pengguna WHERE username=? LIMIT 1");
 $stmt->bind_param("s",$username); $stmt->execute();
 $u = $stmt->get_result()->fetch_assoc();
 if ($u && (password_verify($password,$u['password']) || hash_equals((string)$u['password'],$password))) {
  session_regenerate_id(true);
  $_SESSION['user_id']=(int)$u['id_pengguna']; $_SESSION['nama']=$u['nama_pengguna'];
  header('Location: dashboard.php'); exit;
 }
 $error='Username atau password tidak sesuai.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login FITDAILY</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head>
<body class="auth-bg"><div class="auth-card"><div class="brand justify-content-center mb-4"><span class="brand-mark">F</span><span>FITDAILY<small>Jadwal Olahraga Harian</small></span></div>
<h1 class="h3 text-center fw-bold">Selamat Datang!</h1><p class="text-center muted mb-4">Masuk untuk mengatur aktivitas olahragamu.</p>
<?php if($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
<label class="form-label">Username</label><input class="form-control mb-3" name="username" required autofocus>
<label class="form-label">Password</label><input class="form-control mb-4" type="password" name="password" required>
<button class="btn btn-primary w-100">Masuk ke Dashboard</button></form>
<p class="small text-center mt-3 mb-0">Belum punya akun? <a href="register.php">Daftar di sini</a></p></div></body></html>
