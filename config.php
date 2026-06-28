<?php
// config.php — Database Configuration

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=vector_cbt;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Handle DB connection error gracefully
    die("Database connection failed: " . $e->getMessage());
}
?>
