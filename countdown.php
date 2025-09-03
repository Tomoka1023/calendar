<?php
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/auth.php';
require_login();
$userId = current_user_id();   // ← これを使う
$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT id, title, target_at, color, note, category FROM events WHERE id=?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$ev = $st->fetch();
if (!$ev) { http_response_code(404); exit('Not Found'); }
?>
<!doctype html>
<html lang="ja">
<head>
<link rel="icon" type="image/png" href="favicon.png">
	<meta charset="utf-8">
	<title>
		<?=htmlspecialchars($ev['title'])?> | カウントダウン
	</title>
	<link rel="stylesheet" href="style.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=DotGothic16&family=Hachi+Maru+Pop&family=Hina+Mincho&family=Kaisei+Decol&family=Kiwi+Maru&family=M+PLUS+Rounded+1c&family=Yomogi&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<script defer src="countdown.js"></script>
</head>
<body>
	<div class="container">
		<div class="hero">
			<div class="badge" style="border-color:<?=htmlspecialchars($ev['color']?:'#6c5ce7')?>">ターゲット：<?= (new DateTime($ev['target_at']))->format('Y-m-d H:i:s') ?></div>
			<h1 style="margin-top:10px;">
				<?=htmlspecialchars($ev['title'])?>
			</h1>
			<?php if (($ev['category'] ?? 'normal') !== 'special'): ?>
				<p class="note">このイベントはカウントダウン対象ではありません。</p>
			<?php else: ?>
				<div id="live" class="big" data-countto="<?= (new DateTime($ev['target_at']))->format('c') ?>">--:--:--</div>
			<?php endif; ?>

			
				<!-- ここから編集／削除（小さめ） -->
				<div class="event-actions">
					<a href="event_edit.php?id=<?=$ev['id']?>">編集</a>
					<form action="event_delete.php" method="post" onsubmit="return confirm('この予定を削除します。よろしいですか？');" style="display:inline">
						<input type="hidden" name="id" value="<?=$ev['id']?>">
						<input type="hidden" name="csrf" value="<?=bin2hex(random_bytes(16))?>">
						<button type="submit" class="linklike danger">削除</button>
					</form>
				</div>
			

			<p style="margin-top:18px">
				<a href="index.php">← カレンダーへもどる</a>
			</p>
		</div>
	</div>
</body>
</html>