<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN NOT NULL DEFAULT 0,
        force_password_change BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $pdo->exec($sql);
    echo "Table 'users' created successfully." . PHP_EOL;

    // Add admin user
    $username = 'admin';
    $password = 'admin';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin, force_password_change) VALUES (:username, :password, 1, 1)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password
        ]);
        echo "Admin user created successfully." . PHP_EOL;
    } else {
        echo "Admin user already exists." . PHP_EOL;
    }


} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
