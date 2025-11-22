<?php
session_start();
require_once __DIR__ . '/db/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Username and password are required.";
    header('Location: index.php');
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // For admin user, ensure force_password_change is always off
        if ($user['username'] === 'admin' && $user['force_password_change']) {
            $updateStmt = $pdo->prepare("UPDATE users SET force_password_change = 0 WHERE id = :id");
            $updateStmt->execute([':id' => $user['id']]);
            $user['force_password_change'] = 0; // Update the local variable as well
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // This session variable is no longer strictly needed but left for compatibility
        $_SESSION['force_password_change'] = $user['force_password_change'];

        if ($user['force_password_change']) {
            header("Location: change_password.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $_SESSION['login_error'] = "Invalid username or password.";
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Database error in login.php: " . $e->getMessage());
    $_SESSION['login_error'] = "A database error occurred. Please try again later.";
    header('Location: index.php');
    exit;
}
?>