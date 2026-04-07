<?php
session_start();

// 1. DATABASE CONNECTION
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "chatting";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// 2. CHECK DATA
if (isset($_GET['post_id']) && isset($_SESSION['user_id'])) {

    $post_id = intval($_GET['post_id']);
    $user_id = $_SESSION['user_id'];

    // --- INTAMBWE YA 1: Reba niba uyu muntu yarakoze Like muri ya Table nshya
    $check_query = "SELECT * FROM likes WHERE user_id = $user_id AND post_id = $post_id";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        // Niba asanzwemo, tuyisibe (Unlike)
        $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
    } else {
        // Niba atarimo, moherereze muri table (Like)
        $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    }

    // --- INTAMBWE YA 2: Bara likes zose zihari kuri iyo post muri ya table ya LIKES
    $count_res = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id = $post_id");
    $row_count = $count_res->fetch_assoc();
    $new_count = $row_count['total'];

    // --- INTAMBWE YA 3: Update ya "likes_count" muri table ya posts (kugira ngo bisomeka vuba)
    $conn->query("UPDATE posts SET likes_count = $new_count WHERE post_id = $post_id");

    // 3. RETURN JSON
    echo json_encode([
        'status' => 'success',
        'new_count' => $new_count
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$conn->close();
?>