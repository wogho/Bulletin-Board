<?php
require 'db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT posts.title, posts.content, posts.file_path, users.username 
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        WHERE posts.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $content, $file_path, $username);
$stmt->fetch();

echo "<h1>$title</h1>";
echo "<p>작성자: $username</p>";
echo "<p>$content</p>";

if ($file_path) {
    echo "<a href='$file_path'>첨부파일 다운로드</a>";
}

$stmt->close();
?>
