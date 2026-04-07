<?php
session_start();

// 1. Reba niba umuntu yinjiye n'ubutumwa bushaka gusibwa buhari
if (isset($_SESSION['user_id']) && isset($_POST['msg_id'])) {

    // 2. Database Connection
    $host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "chatting";
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $msg_id = (int) $_POST['msg_id'];

    // 3. SECURITY CHECK: Siba message gusa niba ari wowe wayanditse (sender_id = user_id)
    // Ibi birinda ko umuntu yahindura msg_id akasiba ubutumwa bw'abandi
    $check_sql = "SELECT * FROM messages WHERE msg_id = $msg_id AND sender_id = $user_id";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // A. Siba reactions zose zari kuri ubwo butumwa mbere
        $conn->query("DELETE FROM reactions WHERE message_id = $msg_id");

        // B. Siba ubutumwa nyirizina muri table ya messages
        $delete_sql = "DELETE FROM messages WHERE msg_id = $msg_id AND sender_id = $user_id";

        if ($conn->query($delete_sql)) {
            echo "Success";
        } else {
            echo "Error deleting message: " . $conn->error;
        }
    } else {
        echo "Unauthorized: Ntushobora gusiba ubu butumwa.";
    }

    $conn->close();
} else {
    echo "No message ID provided.";
}
?>