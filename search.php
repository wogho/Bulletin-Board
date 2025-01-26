<?php
require 'db.php';

$search_term = "%" . $_GET['query'] . "%";
$stmt = $conn->prepare("SELECT id, title FROM posts WHERE title LIKE ?");
$stmt->bind_param("s", $search_term);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<h2><a href='view_post.php?id=" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['title']) . "</a></h2>";
}

$stmt->close();
?>

<form method="GET">
    <input type="text" name="query" placeholder="검색어 입력" required>
    <button type="submit">검색</button>
</form>
