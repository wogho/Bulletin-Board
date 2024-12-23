<?php
require 'db.php';
session_start();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    die("잘못된 요청입니다.");
}

// 게시글 불러오기
$stmt = $conn->prepare("SELECT title, content FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($title, $content);

if (!$stmt->fetch()) {
    die("게시글을 찾을 수 없거나 수정 권한이 없습니다.");
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_title = htmlspecialchars($_POST['title'], ENT_QUOTES);
    $new_content = htmlspecialchars($_POST['content'], ENT_QUOTES);

    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_title, $new_content, $id);

    if ($stmt->execute()) {
        header("Location: view_post.php?id=$id");
        exit();
    } else {
        die("수정 실패: " . $stmt->error);
    }

    $stmt->close();
}
?>

<form method="POST">
    <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES) ?>" required>
    <textarea name="content" required><?= htmlspecialchars($content, ENT_QUOTES) ?></textarea>
    <button type="submit">수정</button>
</form>
