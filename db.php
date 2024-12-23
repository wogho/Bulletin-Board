<?php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload 로드

use Dotenv\Dotenv;

// .env 파일 로드
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 환경 변수에서 값 읽기
$servername = $_ENV['DB_SERVER'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("데이터베이스 연결 실패. 관리자에게 문의하세요.");
}
?>
