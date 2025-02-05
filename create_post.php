<?php
require 'db.php';
session_start();

// CSRF 토큰 생성 (없을 경우)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 사용자 인증 확인 및 세션 고정 공격 방지
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
session_regenerate_id(true); // 세션 고정 방지

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("잘못된 요청입니다.");
    }

    // 입력값 필터링 (XSS 방지)
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user_id'];
    $file_path = null;

    // 파일 업로드 처리
    if (!empty($_FILES['file']['name'])) {
        $allowed_ext = ['jpg', 'png', 'pdf'];
        $max_file_size = 1048576; // 1MB 제한

        // 확장자 검사
        $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_ext)) {
            die("잘못된 파일 확장자입니다.");
        }

        // MIME 타입 검사
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($_FILES['file']['type'], $allowed_types)) {
            die("잘못된 파일 형식입니다.");
        }

        // 파일 크기 제한
        if ($_FILES['file']['size'] > $max_file_size) {
            die("파일 크기가 너무 큽니다.");
        }

        // 안전한 파일명 생성
        $file_name = uniqid() . "-" . bin2hex(random_bytes(8)) . "." . $file_ext;
        $target_dir = "uploads/";
        $file_path = $target_dir . $file_name;

        // 파일 이동 및 실행 방지
        if (!move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
            die("파일 업로드에 실패했습니다.");
        }
        chmod($file_path, 0644); // 실행 권한 제거
    }

    // 게시글 데이터 저장
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $content, $file_path);

    if ($stmt->execute()) {
        header("Location: index.php?status=success");
        exit();
    } else {
        error_log("게시글 작성 실패: " . $stmt->error);
        die("게시글 작성 중 문제가 발생했습니다.");
    }

    $stmt->close();
}
?>

<!-- CSRF 보호를 위한 게시글 작성 폼 -->
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
    <input type="text" name="title" placeholder="제목" required>
    <textarea name="content" placeholder="내용" required></textarea>
    <input type="file" name="file">
    <button type="submit">게시글 작성</button>
</form>
