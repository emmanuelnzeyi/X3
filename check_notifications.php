<?php
session_start();
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    $my_id = (int) $_SESSION['user_id'];

    // Barura messages zose zigenewe uyu muntu ariko zitarasomwa (status = 0)
    $sql = "SELECT COUNT(*) AS unread FROM messages WHERE receiver_id = ? AND status = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $my_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();

    // Sohobora umubare gusa
    echo $data['unread'];
} else {
    echo "0";
}

$conn->close();
?>