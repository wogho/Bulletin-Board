<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        die("잘못된 요청입니다.");
    }

    $stmt = $conn->prepare("SELECT user_id, file_path FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($user_id, $file_path);
    $stmt->fetch();
    $stmt->close();

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
        die("권한이 없습니다.");
    }

    // 첨부파일 삭제
    if (!empty($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }

    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: index.php?status=deleted");
        exit();
    } else {
        die("삭제 실패: " . $stmt->error);
    }

    $stmt->close();
}
?>
