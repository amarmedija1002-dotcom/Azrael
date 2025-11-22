<?php
session_start();
require_once __DIR__ . '/db/config.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['force_password_change']) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $error = "Please fill in both fields.";
    } elseif ($password !== $password_confirm) {
        $error = "Passwords do not match.";
    } else {
        try {
            $pdo = db();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password, force_password_change = 0 WHERE id = :id");
            $stmt->execute([
                ':password' => $hashed_password,
                ':id' => $_SESSION['user_id']
            ]);
            $_SESSION['force_password_change'] = 0;
            $success = "Password changed successfully. You will be redirected to the main page in 3 seconds.";
            header("refresh:3;url=index.php");
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
        /* Using the same style as the login page */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #1a1a2e; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; color: white; }
        .container { width: 100%; max-width: 400px; text-align: center; }
        .form-container { background: #1f1f3a; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        h1 { color: #e94560; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 5px; color: #8f8f8f; }
        input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #3a3a5a; background: #1a1a2e; color: white; font-size: 1rem; }
        button { width: 100%; padding: 12px; border: none; border-radius: 8px; background: #e94560; color: white; font-weight: 600; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #c93550; }
        .error { color: #e94560; margin-bottom: 20px; }
        .success { color: #00ff41; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Change Your Password</h1>
            <p style="color: #8f8f8f; margin-bottom: 20px;">As a new user, you must change your password.</p>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php else: ?>
                <form action="change_password.php" method="POST">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm New Password</label>
                        <input type="password" name="password_confirm" id="password_confirm" required>
                    </div>
                    <button type="submit">Change Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>