<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("로그인이 필요합니다.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $file_path = null;

    if (!empty($_FILES['file']['name'])) {
        $target_dir = "uploads/";
        $file_path = $target_dir . basename($_FILES["file"]["name"]);

        // 파일 크기와 형식 검증
        if ($_FILES['file']['size'] > 1048576) { // 1MB 제한
            die("파일 크기가 너무 큽니다.");
        }
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($_FILES['file']['type'], $allowed_types)) {
            die("허용되지 않은 파일 형식입니다.");
        }

        move_uploaded_file($_FILES["file"]["tmp_name"], $file_path);
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $content, $file_path);

    if ($stmt->execute()) {
        echo "게시글이 작성되었습니다.";
        header("Location: index.php");
    } else {
        echo "오류 발생: " . $stmt->error;
    }

    $stmt->close();
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="제목" required>
    <textarea name="content" placeholder="내용" required></textarea>
    <input type="file" name="file">
    <button type="submit">게시글 작성</button>
</form>
