<?php
require_once __DIR__.'/../app/db.php';
date_default_timezone_set('Asia/Tokyo');
require_once __DIR__.'/../app/auth.php';
require_login();
$userId = current_user_id();   // ← これを使う

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

// 所有者チェック込みで削除
$st = $pdo->prepare('DELETE FROM events WHERE id=? AND user_id=?');
$st->execute([$id, $userId]);

// 直近の月へ戻す（適当：今日の年月）
header('Location: index.php?ym='.date('Y-m'));
