<?php
require 'db.php';
session_start();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    die("잘못된 게시글 ID입니다.");
}

$stmt = $conn->prepare("SELECT posts.title, posts.content, posts.file_path, posts.user_id, users.username 
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        WHERE posts.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $content, $file_path, $user_id, $username);

if (!$stmt->fetch()) {
    die("게시글을 찾을 수 없습니다.");
}

echo "<h1>" . htmlspecialchars($title, ENT_QUOTES) . "</h1>";
echo "<p>작성자: " . htmlspecialchars($username, ENT_QUOTES) . "</p>";
echo "<p>" . nl2br(htmlspecialchars($content, ENT_QUOTES)) . "</p>";

if ($file_path) {
    echo "<a href='" . htmlspecialchars($file_path, ENT_QUOTES) . "'>첨부파일 다운로드</a>";
}

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    echo "<form method='POST' action='delete_post.php' style='margin-top: 20px;'>
            <input type='hidden' name='id' value='$id'>
            <button type='submit'>삭제</button>
          </form>";
    echo "<a href='edit_post.php?id=$id' style='margin-top: 10px;'>수정</a>";
}

$stmt->close();
?>
