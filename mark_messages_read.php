<?php
session_start();
$conn = new mysqli("localhost", "root", "", "chatting");

if (isset($_SESSION['user_id'])) {
    $my_id = (int) $_SESSION['user_id'];
    // Hindura messages zose zitarasomwa zibe zasomwe (status = 1)
    $conn->query("UPDATE messages SET status = 1 WHERE receiver_id = $my_id AND status = 0");
    echo "success";
}
?>