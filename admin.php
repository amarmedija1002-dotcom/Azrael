<?php
session_start();
require_once 'db/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

$pdo = db();
$message = '';

// Handle form submission for adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Check if username already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $message = '<p style="color: red;">Username already exists.</p>';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password, force_password_change) VALUES (?, ?, 1)');
            if ($stmt->execute([$username, $hashed_password])) {
                $message = '<p style="color: green;">User added successfully!</p>';
            } else {
                $message = '<p style="color: red;">Failed to add user.</p>';
            }
        }
    } else {
        $message = '<p style="color: red;">Username and password are required.</p>';
    }
}

// Fetch all users to display
$stmt = $pdo->query('SELECT id, username, is_admin, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #2a2a3e; padding: 20px; border-radius: 8px; }
        h1, h2 { color: #e94560; }
        a { color: #e94560; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #444; text-align: left; }
        th { background: #e94560; color: #1a1a2e; }
        form { margin-top: 30px; }
        input[type="text"], input[type="password"] { width: calc(100% - 24px); padding: 12px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #555; background: #333; color: white; }
        button { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; background: #e94560; color: white; }
        .message { margin-bottom: 20px; }
        .logout-link { display: block; text-align: right; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout-link"><a href="logout.php">Logout</a> | <a href="index.php">Chat</a></div>
        <h1>Admin Panel</h1>
        
        <div class="message"><?= $message ?></div>

        <h2>Add New User</h2>
        <form action="admin.php" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Temporary Password" required>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <h2>Manage Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= $user['is_admin'] ? 'Admin' : 'User' ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
