<?php
require 'db.php';
session_start();

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    die("잘못된 요청입니다.");
}

// 게시글 정보 불러오기
$stmt = $conn->prepare("SELECT title, content FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($title, $content);

if (!$stmt->fetch()) {
    die("게시글을 찾을 수 없거나 수정 권한이 없습니다.");
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF 토큰 검증
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("잘못된 요청입니다.");
    }

    $new_title = htmlspecialchars($_POST['title'], ENT_QUOTES);
    $new_content = htmlspecialchars($_POST['content'], ENT_QUOTES);

    // 게시글 업데이트
    $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_title, $new_content, $id);

    if ($stmt->execute()) {
        // 수정 성공 로그
        error_log("Post ID $id updated by user " . $_SESSION['user_id']);
        header("Location: view_post.php?id=$id");
        exit();
    } else {
        die("수정 실패. 관리자에게 문의하세요.");
    }

    $stmt->close();
}
?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES) ?>" required>
    <textarea name="content" required><?= htmlspecialchars($content, ENT_QUOTES) ?></textarea>
    <button type="submit">수정</button>
</form>
