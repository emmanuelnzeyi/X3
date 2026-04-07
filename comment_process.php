<?php
session_start();

// 1. KANDIKA IBIBUZO (ERRORS) KUGIRA NGO BIDAVURUNGANYA JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// 2. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'msg' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {

    $user_id = intval($_SESSION['user_id']);
    $post_id = intval($_POST['post_id']);
    $comment = $conn->real_escape_string($_POST['comment']);

    // 3. INJIZA COMMENT MURI TABLE YA COMMENTS
    $sql = "INSERT INTO comments (post_id, user_id, comment_text) VALUES ($post_id, $user_id, '$comment')";

    if ($conn->query($sql)) {

        // 4. SHAKA NYIRI POST (RECEIVER)
        $find_owner = $conn->query("SELECT user_id FROM posts WHERE post_id = $post_id");

        if ($find_owner && $find_owner->num_rows > 0) {
            $row = $find_owner->fetch_assoc();
            $receiver_id = intval($row['user_id']);

            // 5. INJIZA NOTIFICATION (NIBA ATARI WE WIYANDIKIYE)
            if ($receiver_id !== $user_id) {

                // --- HANO NIHO HAHINDUTSE ---
                // Tubika ijambo 'comment:' rikurikiwe n'amagambo umuntu yanditse
                // Tukata ubutumwa bukaba bugufi (inyuguti 50) kugira ngo bitaba birebire cyane
                $comment_preview = "comment: " . substr($comment, 0, 50);

                $notif_sql = "INSERT INTO notifications (receiver_id, sender_id, post_id, type, is_read, created_at) 
                             VALUES ($receiver_id, $user_id, $post_id, '$comment_preview', 0, NOW())";
                $conn->query($notif_sql);
            }
        }

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'invalid_request']);
}
exit();
?>