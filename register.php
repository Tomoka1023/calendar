<?php
require_once __DIR__.'/../app/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
    $err = 'メール形式が不正、またはパスワードが短すぎます（6文字以上）。';
  } else {
    try {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $st = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
      $st->execute([$email, $hash]);
      header('Location: login.php?registered=1');
      exit;
    } catch (PDOException $e) {
      $err = '登録できませんでした（既に登録済みの可能性があります）。';
    }
  }
}
?>
<!doctype html><html lang="ja">
<head>
<link rel="icon" type="image/png" href="favicon.png">
<meta charset="utf-8">
<title>新規登録</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=DotGothic16&family=Hachi+Maru+Pop&family=Hina+Mincho&family=Kaisei+Decol&family=Kiwi+Maru&family=M+PLUS+Rounded+1c&family=Yomogi&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
</head>
<body>
	<div class="container">
  <form class="form" method="post" style="max-width:420px;margin:40px auto;background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px">
    <h2>新規登録</h2>
    <?php if($err): ?><div class="error"><?=$err?></div><?php endif; ?>
    <label>メール</label>
    <input type="email" name="email" required>
    <label>パスワード（6文字以上）</label>
    <input type="password" name="password" required minlength="6">
    <button class="btn" type="submit">登録する</button>
    <p style="margin-top:10px"><a href="login.php">ログインへ</a></p>
  </form>
</div>
</body>
</html>
