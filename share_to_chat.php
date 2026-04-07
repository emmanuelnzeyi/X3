<?php
session_start();
header('Content-Type: application/json');

// 1. CONNECTION KU DATABASE
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'msg' => 'Database connection failed']);
    exit();
}

// 2. REBA NIBA USER YARINJIYE KANDI AMAKURU YAZIYE MURI POST YAGEMYE
if (isset($_SESSION['user_id']) && isset($_POST['post_id']) && isset($_POST['receiver_id'])) {

    $sender_id = intval($_SESSION['user_id']);
    $receiver_id = intval($_POST['receiver_id']);
    $post_id = intval($_POST['post_id']);

    // 3. SHAKA LINK YA MEDIA (VIDEO/IMAGE) MURI TABLE YA POSTS
    $post_res = $conn->query("SELECT post_image FROM posts WHERE post_id = $post_id");

    if ($post_res && $post_res->num_rows > 0) {
        $post_data = $post_res->fetch_assoc();
        $media_link = $post_data['post_image'];

        // Tubika ubutumwa bukoresheje "shared_post:" imbere
        // Ibi bizatuma muri chatroom tuzahita twerekana video aho kwerekana amagambo
        $msg_text = "shared_post:" . $media_link;

        // 4. INJIZA MU TABLE YA MESSAGES (Hasingiwe ku nkingi zawe: msg_text, sender_id, receiver_id)
        $sql = "INSERT INTO messages (sender_id, receiver_id, msg_text) 
                VALUES ($sender_id, $receiver_id, '$msg_text')";

        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'SQL Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Post not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request or session expired']);
}

$conn->close();
exit();
?>