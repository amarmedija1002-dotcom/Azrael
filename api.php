<?php
session_start();
require_once 'db/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo = db();
    switch ($action) {
        case 'get_my_unique_id':
            $stmt = $pdo->prepare("SELECT unique_id FROM users WHERE id = ?");
            $stmt->execute([$current_user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user);
            break;

        case 'search_users':
            $query = $_GET['query'] ?? '';
            $stmt = $pdo->prepare("SELECT id, username, unique_id FROM users WHERE unique_id LIKE ? AND id != ?");
            $stmt->execute(["%$query%", $current_user_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
            break;

        case 'send_friend_request':
            $data = json_decode(file_get_contents('php://input'), true);
            $user_to_add_id = (int)($data['user_id'] ?? 0);

            if ($user_to_add_id === 0) {
                echo json_encode(['error' => 'Invalid user ID']);
                exit;
            }

            // Check if a request already exists
            $stmt = $pdo->prepare("SELECT id FROM friends WHERE (user_one_id = ? AND user_two_id = ?) OR (user_one_id = ? AND user_two_id = ?)");
            $stmt->execute([$current_user_id, $user_to_add_id, $user_to_add_id, $current_user_id]);
            if ($stmt->fetch()) {
                echo json_encode(['error' => 'Friend request already sent or you are already friends.']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO friends (user_one_id, user_two_id, status, action_user_id) VALUES (?, ?, 'pending', ?)");
            $stmt->execute([$current_user_id, $user_to_add_id, $current_user_id]);

            echo json_encode(['success' => true, 'message' => 'Friend request sent.']);
            break;

        case 'get_friend_requests':
            $stmt = $pdo->prepare("
                SELECT f.id, u.username, u.unique_id
                FROM friends f
                JOIN users u ON f.action_user_id = u.id
                WHERE f.user_two_id = ? AND f.status = 'pending' AND f.action_user_id != ?
            ");
            $stmt->execute([$current_user_id, $current_user_id]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($requests);
            break;

        case 'update_friend_request':
            $data = json_decode(file_get_contents('php://input'), true);
            $request_id = (int)($data['request_id'] ?? 0);
            $status = $data['status'] ?? ''; // 'accepted' or 'declined'

            if ($request_id === 0 || !in_array($status, ['accepted', 'declined'])) {
                echo json_encode(['error' => 'Invalid input']);
                exit;
            }

            if ($status === 'accepted') {
                $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE id = ? AND user_two_id = ?");
                $stmt->execute([$request_id, $current_user_id]);
            } else { // declined
                $stmt = $pdo->prepare("DELETE FROM friends WHERE id = ? AND user_two_id = ?");
                $stmt->execute([$request_id, $current_user_id]);
            }

            echo json_encode(['success' => true, 'message' => 'Friend request ' . $status]);
            break;

        case 'get_friends':
            $stmt = $pdo->prepare("
                SELECT u.id, u.username
                FROM friends f
                JOIN users u ON (u.id = f.user_one_id OR u.id = f.user_two_id)
                WHERE (f.user_one_id = ? OR f.user_two_id = ?)
                  AND f.status = 'accepted'
                  AND u.id != ?
            ");
            $stmt->execute([$current_user_id, $current_user_id, $current_user_id]);
            $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($friends);
            break;

        case 'get_messages':
            $recipient_id = (int)($_GET['user_id'] ?? 0);
            if ($recipient_id === 0) {
                echo json_encode(['error' => 'Invalid user ID']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY created_at ASC");
            $stmt->execute([$current_user_id, $recipient_id, $recipient_id, $current_user_id]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($messages);
            break;

        case 'send_message':
            $recipient_id = (int)($_POST['recipient_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            $image_url = null;

            if ($recipient_id === 0) {
                echo json_encode(['error' => 'Invalid recipient ID']);
                exit;
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0775, true);
                }

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($_FILES['image']['tmp_name']);
                if (!in_array($file_type, $allowed_types)) {
                    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
                    exit;
                }

                $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = 'uploads/' . $file_name;
                } else {
                    echo json_encode(['error' => 'Failed to upload image.']);
                    exit;
                }
            }

            if (empty($message) && !$image_url) {
                echo json_encode(['error' => 'Message cannot be empty.']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, message, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$current_user_id, $recipient_id, $message, $image_url]);

            echo json_encode(['success' => true, 'message' => 'Message sent']);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}