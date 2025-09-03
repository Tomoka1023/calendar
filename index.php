<?php
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/helpers.php';
require_once __DIR__.'/../app/auth.php';
require_login();
$userId = current_user_id();   // ← これを使う
date_default_timezone_set('Asia/Tokyo');
$ym = ym_from_query();
[$prevYm, $nextYm] = prev_next_links($ym);
$dates = month_dates($ym);
$today = new DateTime('today');

// 月の範囲（target_at の日付で抽出）
$start = new DateTime($ym.'-01');
$end = (clone $start)->modify('last day of this month')->setTime(23,59,59);

$st = $pdo->prepare("SELECT id, target_at, title, color, category
                     FROM events
                     WHERE user_id=? AND target_at BETWEEN ? AND ?
                     ORDER BY target_at ASC");
$st->execute([$userId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);
$events = $st->fetchAll();

// 日付 => events
$eventsByDate = [];
foreach ($events as $ev) {
$d = (new DateTime($ev['target_at']))->format('Y-m-d');
$eventsByDate[$d][] = $ev;
}

// 近日（今から先の10件）
$st2 = $pdo->prepare("SELECT id, target_at, title, color, category
                      FROM events
                      WHERE user_id=? AND category='special' AND target_at >= NOW()
                      ORDER BY target_at ASC
                      LIMIT 10");
$st2->execute([$userId]);
$nextEvents = $st2->fetchAll();
?>

<!doctype html>
<html lang="ja">
<head>
<link rel="icon" type="image/png" href="favicon.png">
	<meta charset="utf-8">
	<title>スケジュール | <?=$ym?></title>

	<link rel="manifest" href="manifest.webmanifest">
	<meta name="theme-color" content="#c9ec72">
	<link rel="icon" type="image/png" href="favicon.png">
	<!-- iOS向け（ホーム追加用） -->
	<link rel="apple-touch-icon" href="favicon.png">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">

	<link rel="stylesheet" href="style.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&family=DotGothic16&family=Hachi+Maru+Pop&family=Hina+Mincho&family=Kaisei+Decol&family=Kiwi+Maru&family=M+PLUS+Rounded+1c&family=Yomogi&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<script defer src="countdown.js"></script>
</head>

<body>
<div class="container"
		id="app"
    data-prev="<?=$prevYm?>"
    data-next="<?=$nextYm?>"
    data-this="<?=date('Y-m')?>">
	<div class="header">
		<div class="nav">
			<a class="beforemnt" href="?ym=<?=$prevYm?>">◀ <?=$prevYm?></a>
			<a class="current" href="?ym=<?=$ym?>"><?=$ym?></a>
			<a class="aftermnt" href="?ym=<?=$nextYm?>"><?=$nextYm?> ▶</a>
			<!-- <a class="today-btn" href="?ym=<?=date('Y-m')?>">今月へ</a> -->
		</div>
		<div class="actions">
			<a class="btn" href="event_new.php">＋ 予定を追加</a>
			<a class="btn cancel" href="logout.php" style="margin-left:8px">ログアウト</a>
		</div>
		<h1><?=$ym?> のカレンダー</h1>
	</div>

	<!-- 画面右下に常時表示する“今月へ” -->
  <a href="?ym=<?=date('Y-m')?>" class="fab-today" aria-label="今月へ">今月へ</a>

	<!-- 近日のイベント -->
	<section class="next-up">
		<h2>特別な日まで</h2>
		<div class="next-list">
			<?php if (!$nextEvents): ?>
				<div class="card">予定はありません</div>
			<?php else: foreach ($nextEvents as $ev):
				$t = new DateTime($ev['target_at']);
				$diff = $t->getTimestamp() - time();
				$days = (int)floor($diff/86400);
				$badge = $days >= 0 ? 'D-'.max($days,0) : '終了';
			?>
			<div class="card">
				<div class="badge <?=($days<=3 && $days>=0)?'soon':''?>" style="border-color:<?=htmlspecialchars($ev['color']?:'#6c5ce7')?>">
					<?=$badge?>
				</div>
					<h3 style="margin:8px 0 6px;font-size:15px;">
						<a href="countdown.php?id=<?=$ev['id']?>"><?=htmlspecialchars($ev['title'])?></a>
					</h3>
					<div class="count-mini" data-countto="<?=$t->format('c')?>"></div>
					<div style="font-size:12px;color:var(--muted)"><?=$t->format('Y-m-d H:i')?></div>
				</div>
				<?php endforeach; endif; ?>
			</div>
	</section>

	<!-- カレンダー -->
	<div class="calendar">
		<?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $w) echo '<div class="weekday">'.$w.'</div>'; ?>
		<?php $firstDow = (int)(new DateTime("$ym-01"))->format('w'); for ($i=0; $i<$firstDow; $i++) echo '<div></div>'; ?>

		<?php foreach ($dates as $d):
			$dStr = $d->format('Y-m-d');
			$isToday = is_same_day($d, $today);
			$weekdayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
			$wname = $weekdayNames[(int)$d->format('w')]; // ← ここで曜日文字を用意
			$eventsToday = $eventsByDate[$dStr] ?? [];
		?>
		<div class="day<?= $isToday ? ' today' : '' ?>">
			<div class="date"><?= $d->format('j') ?> (<?= $wname ?>)</div>

			<?php if (!empty($eventsToday)): foreach ($eventsToday as $ev):
        $t = new DateTime($ev['target_at']);
        $color = $ev['color'] ?: '#6c5ce7';
        $badgeHtml = '';
        if (($ev['category'] ?? 'normal') === 'special') {
          $days = (int)floor(($t->getTimestamp() - time())/86400);
          $badge = $days >= 0 ? 'D-'.max($days,0) : '終了';
          $badgeHtml = '<span class="time badge" style="border-color:'.htmlspecialchars($color).';margin-right:6px;">'.$badge.'</span>';
        }
      ?>

				<div class="event" style="background:<?=htmlspecialchars($color)?>50;border-left:4px solid <?=htmlspecialchars($color)?>;">
					<a href="countdown.php?id=<?=$ev['id']?>">
						<?= $badgeHtml ?><?= htmlspecialchars($ev['title']) ?>
					</a>
				</div>
			<?php endforeach; endif; ?>

			<!-- ★ スマホ用：色ドット（最大3つ） -->
			<?php if (!empty($eventsToday)): ?>

				<script type="application/json" id="ev-<?=$dStr?>">
					<?= json_encode(array_map(function($e){
							return [
								'id'        => (int)$e['id'],
								'title'     => $e['title'],
								'target_at' => (new DateTime($e['target_at']))->format('Y-m-d H:i'),
								'color'     => $e['color'] ?: '#6c5ce7',
								'category'  => $e['category'] ?? 'normal',
							];
						}, $eventsToday), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>
				</script>

				<div class="event-dots" data-day="<?=$dStr?>" aria-label="<?=count($eventsToday)?>件の予定">
					<?php
						$i = 0;
						foreach ($eventsToday as $ev) {
							if ($i >= 3) break;
							$c = $ev['color'] ?: '#6c5ce7';
							echo '<span class="dot" style="background:'.htmlspecialchars($c).';"></span>';
							$i++;
						}
						if (count($eventsToday) > 3) {
							echo '<span class="more">+'.(count($eventsToday)-3).'</span>';
						}
					?>
				</div>
		<?php endif; ?>

	</div>
	<?php endforeach; ?>

		<?php $lastDow = (int)$dates[count($dates)-1]->format('w'); for ($i=$lastDow; $i<6; $i++) echo '<div></div>'; ?>

</div>

<!-- モーダル土台 -->
<div id="dayModal" class="modal" hidden>
  <div class="modal__backdrop"></div>
  <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal__header">
      <h3 id="modalTitle">この日の予定</h3>
      <button type="button" class="modal__close" aria-label="閉じる">×</button>
    </div>
    <div class="modal__body" id="modalBody"></div>
  </div>
</div>

<script>
(() => {
  const root = document.getElementById('app');
  const prevYm = root.dataset.prev;
  const nextYm = root.dataset.next;

  // スワイプ設定
  const THRESHOLD_X = 60;   // 横にこれ以上動いたらスワイプ扱い
  const LIMIT_Y     = 40;   // 縦のブレがこれ超えたらキャンセル
  let startX = 0, startY = 0, dragging = false, moved = false;

  // スワイプ対象（カレンダー全体 + 近日カードあたり）
  const swipeArea = document.querySelector('.calendar') || root;

  function onDown(x, y){
    startX = x; startY = y; dragging = true; moved = false;
  }
  function onMove(x, y){
    if (!dragging) return;
    const dx = x - startX;
    const dy = Math.abs(y - startY);
    if (dy > LIMIT_Y) { dragging = false; return; }
    if (Math.abs(dx) > THRESHOLD_X) {
      moved = true;
      dragging = false;
      if (dx < 0 && nextYm) {
        window.location.search = '?ym=' + encodeURIComponent(nextYm); // 左→右へスワイプ（指は左→右？逆？）: ここは dx<0 を「左へスワイプ→次月」扱い
      } else if (dx > 0 && prevYm) {
        window.location.search = '?ym=' + encodeURIComponent(prevYm);
      }
    }
  }
  function onUp(){ dragging = false; }

  // タッチ
  swipeArea.addEventListener('touchstart', (e) => {
    const t = e.changedTouches[0];
    onDown(t.clientX, t.clientY);
  }, {passive:true});
  swipeArea.addEventListener('touchmove', (e) => {
    const t = e.changedTouches[0];
    onMove(t.clientX, t.clientY);
  }, {passive:true});
  swipeArea.addEventListener('touchend', onUp, {passive:true});
  swipeArea.addEventListener('touchcancel', onUp, {passive:true});

  // マウス（PCでもドラッグで動かしたい場合）
  swipeArea.addEventListener('mousedown', (e) => onDown(e.clientX, e.clientY));
  window.addEventListener('mousemove', (e) => onMove(e.clientX, e.clientY));
  window.addEventListener('mouseup', onUp);

  // 方向を入れ替えたいなら↑の dx 判定を逆にしてね。
})();
</script>

<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js').catch(console.error);
  });
}
</script>

</body>
</html>