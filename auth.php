<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function current_user_id(): ?int {
  return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}
function require_login(): void {
  if (!current_user_id()) {
    header('Location: login.php');
    exit;
  }
}
function login_user(int $uid): void {
  $_SESSION['user_id'] = $uid;
}
function logout_user(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
  }
  session_destroy();
}
