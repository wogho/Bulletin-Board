<?php
// 데이터베이스 연결 및 세션 시작
require 'db.php';
session_start();

// 로그아웃 처리
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 현재 로그인 상태 확인
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin Board</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
        <h1>Welcome to the Bulletin Board!</h1>
    </header>
    <div class="container">
        <!-- 사용자 인증 영역 -->
        <div class="auth">
            <?php if ($is_logged_in): ?>
                <p>환영합니다, 사용자님! <a href="?logout=true">로그아웃</a></p>
            <?php else: ?>
                <p>
                    <a href="register.php">회원가입</a> | 
                    <a href="login.php">로그인</a>
                </p>
            <?php endif; ?>
        </div>

        <!-- 검색 폼 -->
        <form method="GET" action="search.php">
            <input type="text" name="query" placeholder="게시글 검색" required>
            <button type="submit">검색</button>
        </form>

        <!-- 게시글 생성 버튼 -->
        <?php if ($is_logged_in): ?>
            <form method="POST" action="create_post.php" enctype="multipart/form-data" class="create-form">
                <input type="text" name="title" placeholder="제목" required>
                <textarea name="content" placeholder="내용" required></textarea>
                <input type="file" name="file">
                <button type="submit">게시글 작성</button>
            </form>
        <?php endif; ?>

        <!-- 게시글 목록 -->
        <ul class="post-list">
            <?php
            $result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
            while ($row = $result->fetch_assoc()):
            ?>
                <li>
                    <h3><a href="view_post.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
                    <p><?= htmlspecialchars($row['content']) ?></p>
                    <small>작성일: <?= $row['created_at'] ?></small>
                    <?php if ($is_logged_in && $row['user_id'] == $_SESSION['user_id']): ?>
                        <!-- 수정 및 삭제 버튼 -->
                        <form method="POST" action="edit_post.php">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit">수정</button>
                        </form>
                        <form method="POST" action="delete_post.php" onsubmit="return confirm('게시글을 삭제하시겠습니까?');">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit">삭제</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <footer>
        <p>&copy; 2024 Bulletin Board. All rights reserved.</p>
    </footer>
</body>
</html>
