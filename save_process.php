<?php
session_start();

// 1. DATABASE CONNECTION
// Reba niba amakuru ya database yawe ariyo (localhost, root, "", chatting)
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// 2. SECURITY CHECK
// Reba niba user ari logged in kandi niba post_id yageze hano
if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing data']);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$post_id = intval($_GET['post_id']);

// 3. CHECK IF ALREADY SAVED (Kureba niba isanzwe muri table)
// Emeza ko table yawe yitwa 'saved_posts'
$check_query = "SELECT * FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id";
$check_res = $conn->query($check_query);

header('Content-Type: application/json');

if ($check_res && $check_res->num_rows > 0) {
    // A. NIBA ISANZWE IRIMO -> TUYISIBE (UNSAVE)
    $delete_query = "DELETE FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id";
    if ($conn->query($delete_query)) {
        echo json_encode(['status' => 'unsaved']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    // B. NIBA ITARIMO -> TUYISHYIREMO (SAVE)
    $insert_query = "INSERT INTO saved_posts (user_id, post_id) VALUES ($user_id, $post_id)";
    if ($conn->query($insert_query)) {
        echo json_encode(['status' => 'saved']);
    } else {
        echo json_encode(['status' => 'error']);
    }
}

$conn->close();
?>