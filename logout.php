<?php
session_start();

// CSRF 토큰 생성 (없을 경우)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF 토큰 검증 (POST 방식 로그아웃 요청)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("잘못된 요청입니다.");
    }
}

// 모든 세션 변수 제거
$_SESSION = array();

// 세션 쿠키 제거
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 세션 종료 및 새로운 세션 강제 생성
session_destroy();
session_start();
session_regenerate_id(true); // 새 세션 ID 부여

// 안전한 리다이렉트 처리
$redirect = 'index.php';
if (isset($_GET['redirect']) && filter_var($_GET['redirect'], FILTER_VALIDATE_URL)) {
    $redirect = $_GET['redirect'];
}
header("Location: " . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'));
exit();
?>

<!-- CSRF 보호를 위한 로그아웃 버튼 -->
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
    <button type="submit">로그아웃</button>
</form>
