<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    // ID 검증
    if ($id === false || $id <= 0) {
        die("잘못된 요청입니다.");
    }

    // 게시글 정보 확인
    $stmt = $conn->prepare("SELECT user_id, file_path FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($user_id, $file_path);
    $stmt->fetch();
    $stmt->close();

    // 권한 확인
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
        die("권한이 없습니다.");
    }

    // 파일 경로 검증 및 삭제
    if (!empty($file_path)) {
        $real_path = realpath($file_path);
        $allowed_path = '/var/www/uploads/'; // 허용된 디렉토리
        if ($real_path && strpos($real_path, $allowed_path) === 0 && file_exists($real_path)) {
            unlink($real_path);
        } else {
            die("파일 경로가 유효하지 않습니다.");
        }
    }

    // 게시글 삭제
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // 삭제 성공 로그
        error_log("Post ID $id deleted by user " . $_SESSION['user_id']);
        header("Location: index.php?status=deleted");
        exit();
    } else {
        die("삭제 실패. 관리자에게 문의하세요.");
    }

    $stmt->close();
}
?>
