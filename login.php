<?php
require 'db.php';
session_start();

// 로그인 시도 제한 변수 초기화
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 브루트포스 방지: 시도 횟수 초과 확인
    if ($_SESSION['login_attempts'] >= 5) {
        die("로그인 시도 횟수를 초과했습니다. 잠시 후 다시 시도해주세요.");
    }

    if (empty($username) || empty($password)) {
        die("아이디와 비밀번호를 입력해주세요.");
    }

    // 응답 시간 통일
    sleep(1);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // 로그인 성공: 세션 ID 재생성 및 리디렉션
            session_regenerate_id(true);
            $_SESSION['user_id'] = $id;
            $_SESSION['login_attempts'] = 0; // 시도 횟수 초기화
            header("Location: index.php");
            exit();
        }
    }

    // 로그인 실패 처리
    $_SESSION['login_attempts'] += 1; // 시도 횟수 증가
    echo "아이디 또는 비밀번호가 올바르지 않습니다.";
    $stmt->close();
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="아이디" required>
    <input type="password" name="password" placeholder="비밀번호" required>
    <button type="submit">로그인</button>
</form>
