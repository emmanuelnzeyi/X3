<?php
session_start();

// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. SECURITY CHECK
if (isset($_SESSION['user_id']) && isset($_POST['post_id'])) {

    $user_id = $_SESSION['user_id'];
    $post_id = intval($_POST['post_id']); // Guhindura post_id mu mibare (Integer) kuko ari int(11)

    // 3. CHECK IF ALREADY SAVED
    // Reba niba umuntu asanzwe yarayisavinze
    $check_sql = "SELECT * FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id";
    $check_query = $conn->query($check_sql);

    if ($check_query->num_rows > 0) {
        // Niba isanzwe irimo, uyikuremo (UNSAVE)
        $delete_sql = "DELETE FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id";
        if ($conn->query($delete_sql)) {
            echo "unsaved";
        }
    } else {
        // Niba itarimo, uyishiremo (SAVE)
        // Inkingi 'id' na 'saved_at' zizahita zuzura zonyine (Auto-increment & Current Timestamp)
        $insert_sql = "INSERT INTO saved_posts (user_id, post_id) VALUES ($user_id, $post_id)";
        if ($conn->query($insert_sql)) {
            echo "saved";
        }
    }
} else {
    echo "error";
}

$conn->close();
?>