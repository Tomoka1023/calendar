<?php
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/auth.php';
require_login();
$userId = current_user_id();   // ← これを使う

$date_only = $_POST['date_only'] ?? null;          // YYYY-MM-DD
$time_only = $_POST['time_only'] ?? '';            // HH:MM or ''
$title     = trim($_POST['title'] ?? '');
$note      = $_POST['note'] ?? null;
$color     = $_POST['color'] ?: null;
$category  = ($_POST['category'] ?? 'normal') === 'special' ? 'special' : 'normal'; // 使ってる場合

if (!$date_only || !$title) { header('Location: index.php'); exit; }

// 時間が空なら 00:00:00 を使う（丸一日や時間未定のケース）
$time_part = $time_only !== '' ? $time_only.':00' : '00:00:00';
$target_at = $date_only . ' ' . $time_part;

$st = $pdo->prepare(
  'INSERT INTO events (user_id, target_at, title, note, color, category)
   VALUES (?, ?, ?, ?, ?, ?)'
);
$st->execute([$userId, $target_at, $title, $note, $color, $category]);

header('Location: index.php?ym='.substr($date_only,0,7));
