<?php
include "db_connect.php";

try {
  // Create PDO connection
  try {
    // Create PDO connection
    $pdo = new PDO(
      "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
      $username,
      $password,
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
      ]
    );
  } catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
  }
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}
?>
