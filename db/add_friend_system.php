<?php
require_once 'config.php';

try {
    $pdo = db();

    // Check if the column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'unique_id'");
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        // Drop the unique key if it exists
        try {
            $pdo->exec("ALTER TABLE users DROP KEY unique_id");
        } catch (PDOException $e) {
            // Ignore error if the key doesn't exist
        }
        // Drop the column
        $pdo->exec("ALTER TABLE users DROP COLUMN unique_id");
        echo "Dropped existing unique_id column.\n";
    }

    // Add unique_id column to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN unique_id VARCHAR(16) NOT NULL AFTER id");
    echo "Added unique_id column to users table.\n";

    // Populate unique_id for existing users
    $stmt = $pdo->query("SELECT id FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $pdo->prepare("UPDATE users SET unique_id = ? WHERE id = ?");

    foreach ($users as $user) {
        $unique_id = substr(md5(uniqid(rand(), true)), 0, 8);
        $updateStmt->execute([$unique_id, $user['id']]);
    }
    echo "Populated unique_id for existing users.\n";

    // Add unique constraint
    $pdo->exec("ALTER TABLE users ADD UNIQUE (unique_id)");
    echo "Added unique constraint to unique_id column.\n";


    // Create friends table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS friends (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_one_id INT NOT NULL,
            user_two_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'declined', 'blocked') NOT NULL,
            action_user_id INT NOT NULL,
            FOREIGN KEY (user_one_id) REFERENCES users(id),
            FOREIGN KEY (user_two_id) REFERENCES users(id),
            FOREIGN KEY (action_user_id) REFERENCES users(id),
            UNIQUE KEY unique_friendship (user_one_id, user_two_id)
        )
    ");
    echo "Created friends table.\n";

    echo "Friend system database setup complete!\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}