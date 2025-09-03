<?php
require_once __DIR__.'/../app/db.php';
date_default_timezone_set('Asia/Tokyo');
require_once __DIR__.'/../app/auth.php';
require_login();
$userId = current_user_id();   // ← これを使う

$id        = (int)($_POST['id'] ?? 0);
$date_only = $_POST['date_only'] ?? null;
$time_only = $_POST['time_only'] ?? '';
$title     = trim($_POST['title'] ?? '');
$note      = $_POST['note'] ?? null;
$color     = $_POST['color'] ?: '#6c5ce7';
$category  = ($_POST['category'] ?? 'normal') === 'special' ? 'special' : 'normal';

if (!$id || !$date_only || !$title) { header('Location: index.php'); exit; }

$time_part = $time_only !== '' ? $time_only.':00' : '00:00:00';
$target_at = $date_only.' '.$time_part;

// 所有者チェックしつつ更新
$st = $pdo->prepare('UPDATE events SET target_at=?, title=?, note=?, color=?, category=? WHERE id=? AND user_id=?');
$st->execute([$target_at, $title, $note, $color, $category, $id, $userId]);

header('Location: index.php?ym='.substr($date_only,0,7));
