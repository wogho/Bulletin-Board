<?php
$servername = "129.154.215.197";
$username = "root";
$password = "Wldhr@12";
$dbname = "bulletin_board";

// 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
