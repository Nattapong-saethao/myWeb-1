<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "DB_HOST: " . $_ENV["DB_HOST"] . "<br>";
echo "DB_USER: " . $_ENV["DB_USER"] . "<br>";
echo "DB_PASSWORD: " . $_ENV["DB_PASSWORD"] . "<br>";
echo "DB_NAME: " . $_ENV["DB_NAME"] . "<br>";

$servername = $_ENV["DB_HOST"];
$username = $_ENV["DB_USER"];
$password = $_ENV["DB_PASSWORD"];
$dbname = $_ENV["DB_NAME"];

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
