<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES);
    $content = htmlspecialchars(trim($_POST['content']), ENT_QUOTES);
    $user_id = $_SESSION['user_id'];
    $file_path = null;

    if (!empty($_FILES['file']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $max_file_size = 1048576;
        if (!in_array($_FILES['file']['type'], $allowed_types) || $_FILES['file']['size'] > $max_file_size) {
            die("허용되지 않는 파일 형식이거나 파일 크기가 너무 큽니다.");
        }

        $file_name = uniqid() . "-" . basename($_FILES["file"]["name"]);
        $target_dir = "uploads/";
        $file_path = $target_dir . $file_name;

        if (!move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
            die("파일 업로드에 실패했습니다.");
        }
    }

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

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="제목" required>
    <textarea name="content" placeholder="내용" required></textarea>
    <input type="file" name="file">
    <button type="submit">게시글 작성</button>
</form>
