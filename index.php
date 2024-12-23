<?php
require 'db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Bulletin-Board</title>
</head>
<body>
    <header>
        <h1>Bulletin-Board</h1>
    </header>
    <main>
        <!-- 사용자 인증 상태 표시 -->
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>환영합니다! <a href="logout.php">로그아웃</a></p>
                <!-- 게시글 작성 폼 -->
                <form method="POST" action="create_post.php" enctype="multipart/form-data" class="create-form">
                    <input type="text" name="title" placeholder="제목" required>
                    <textarea name="content" placeholder="내용" required></textarea>
                    <input type="file" name="file">
                    <button type="submit">게시글 작성</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">로그인</a> | <a href="register.php">회원가입</a></p>
            <?php endif; ?>
        </div>

        <!-- 검색 폼 -->
        <form method="GET" action="search.php">
            <input type="text" name="query" placeholder="검색어를 입력하세요" required>
            <button type="submit">검색</button>
        </form>

        <!-- 게시글 목록 -->
        <ul>
            <?php
            $stmt = $conn->prepare("SELECT id, title, created_at FROM posts ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()): ?>
                <li>
                    <h3><a href="view_post.php?id=<?= htmlspecialchars($row['id'], ENT_QUOTES) ?>">
                        <?= htmlspecialchars($row['title'], ENT_QUOTES) ?></a></h3>
                    <small>작성일: <?= htmlspecialchars($row['created_at'], ENT_QUOTES) ?></small>
                </li>
            <?php endwhile;

            $stmt->close();
            ?>
        </ul>
    </main>
    <footer>
        <p>&copy; 2024 Bulletin-Board. All rights reserved.</p>
    </footer>
</body>
</html>
