<?php
require 'db.php';
session_start();

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF 토큰 검증
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("잘못된 요청입니다.");
    }

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 입력값 검증
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        die("아이디는 3~20자의 영문, 숫자, 밑줄만 가능합니다.");
    }
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\\d)(?=.*[\\W_]).{8,}$/', $password)) {
        die("비밀번호는 최소 8자이며 대문자, 소문자, 숫자, 특수문자를 포함해야 합니다.");
    }

    // 응답 시간 통일
    sleep(1);

    // 중복 아이디 확인
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        die("회원가입 실패. 다시 시도하세요.");
    }
    $check_stmt->close();

    // 비밀번호 해싱 및 데이터베이스 삽입
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        echo "회원가입 성공!";
        header("Location: login.php");
        exit();
    } else {
        error_log("회원가입 실패: " . $stmt->error);
        die("회원가입 중 문제가 발생했습니다.");
    }

    $stmt->close();
}
?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
    <input type="text" name="username" placeholder="아이디" required>
    <input type="password" name="password" placeholder="비밀번호" required>
    <button type="submit">회원가입</button>
</form>
