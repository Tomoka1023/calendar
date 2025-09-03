<?php
require_once __DIR__.'/../app/db.php';
date_default_timezone_set('Asia/Tokyo');
require_once __DIR__.'/../app/auth.php';
require_login();
$userId = current_user_id();   // ← これを使う

$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM events WHERE id=? AND user_id=?');
$st->execute([$id, $userId]);
$ev = $st->fetch();
if (!$ev) { http_response_code(404); exit('Not Found'); }

// target_at → date & time へ分離
$dt = new DateTime($ev['target_at']);
$date_only = $dt->format('Y-m-d');
$time_only = $dt->format('H:i');
?>
<!doctype html>
<html lang="ja">
<head>
<link rel="icon" type="image/png" href="favicon.png">
	<meta charset="utf-8">
	<title>予定を編集</title>
	<link rel="stylesheet" href="style.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=DotGothic16&family=Hachi+Maru+Pop&family=Hina+Mincho&family=Kaisei+Decol&family=Kiwi+Maru&family=M+PLUS+Rounded+1c&family=Yomogi&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
</head>
<body>
<div class="container">
  <form class="form" action="event_update.php" method="post" style="max-width:560px;margin:24px auto;background:#ffffffe8;border:1px solid var(--border);border-radius:12px;padding:16px">
    <h2>予定を編集</h2>
    <input type="hidden" name="id" value="<?=htmlspecialchars($ev['id'])?>">
    <input type="hidden" name="csrf" value="<?=bin2hex(random_bytes(16))?>">

    <label>日付</label>
    <input type="date" name="date_only" value="<?=htmlspecialchars($date_only)?>" required>

    <label>時間</label>
    <input type="time" name="time_only" value="<?=htmlspecialchars($time_only)?>">

    <label>タイトル</label>
    <input type="text" name="title" maxlength="255" value="<?=htmlspecialchars($ev['title'])?>" required>

    <label>カテゴリ</label>
		<select id="category" name="category">
			<option value="normal" selected data-custom-properties="normal">通常</option>
			<option value="special" data-custom-properties="special">特別（カウントダウン対象）</option>
		</select>

    <label>メモ</label>
    <textarea name="note" rows="4"><?=htmlspecialchars($ev['note']??'')?></textarea>

    <label>色</label>
		<div class="color-options">
			<label><input type="radio" name="color" value="#ff8a94" checked><span style="background:#ff8a94"></span></label>
			<label><input type="radio" name="color" value="#ffdc80"><span style="background:#ffdc80"></span></label>
			<label><input type="radio" name="color" value="#ffff6b"><span style="background:#ffff6b"></span></label>
			<label><input type="radio" name="color" value="#6eff9d"><span style="background:#6eff9d"></span></label>
			<label><input type="radio" name="color" value="#6ec9ff"><span style="background:#6ec9ff"></span></label>
			<label><input type="radio" name="color" value="#a469f7"><span style="background:#a469f7"></span></label>
			<label><input type="radio" name="color" value="#cc80ff"><span style="background:#cc80ff"></span></label>
		</div>

    <div style="display:flex;gap:8px;margin-top:12px;justify-content:space-between;align-items:flex-end;">
      <button type="submit" class="btn">更新</button>
      <a href="index.php?ym=<?=$dt->format('Y-m')?>"style="color:var(--text);">キャンセル</a>
    </div>
  </form>
</div>
<script>
  const element = document.getElementById('category');
  const choices = new Choices(element, {
    searchEnabled: false,
    itemSelectText: '',
    callbackOnCreateTemplates: function (template) {
      return {
        choice: (classNames, data) => {
          return template(`
            <div class="${classNames.item} ${classNames.itemChoice}"
              data-select-text=""
              data-choice
              data-id="${data.id}"
              data-value="${data.value}"
              data-custom-class="${data.customProperties}"
              role="option">
              ${data.label}
            </div>
          `);
        }
      };
    }
  });
</script>
</body>
</html>
