<?php
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/auth.php';

$info = isset($_GET['registered']) ? '登録が完了しました。ログインしてください。' : '';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $st = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
  $st->execute([$email]);
  $u = $st->fetch();
  if ($u && password_verify($pass, $u['password_hash'])) {
    login_user((int)$u['id']);
    header('Location: index.php');
    exit;
  } else {
    $err = 'メールまたはパスワードが違います。';
  }
}
?>
<!doctype html>
<html lang="ja">
<head>
<link rel="icon" type="image/png" href="favicon.png">
<meta charset="utf-8">
<title>ログイン</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=DotGothic16&family=Hachi+Maru+Pop&family=Hina+Mincho&family=Kaisei+Decol&family=Kiwi+Maru&family=M+PLUS+Rounded+1c&family=Yomogi&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
</head>
<body>
	<div class="container">
  <form class="form" method="post" style="max-width:420px;margin:40px auto;background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px">
    <h2>ログイン</h2>
    <?php if($info): ?><div class="info"><?=$info?></div><?php endif; ?>
    <?php if($err): ?><div class="error"><?=$err?></div><?php endif; ?>
    <label>メール</label>
    <input type="email" name="email" required>
    <label>パスワード</label>
    <input type="password" name="password" required>
    <button class="btn" type="submit">ログイン</button>
    <p style="margin-top:10px"><a href="register.php">新規登録はこちら</a></p>
  </form>
</div>
</body>
</html>
