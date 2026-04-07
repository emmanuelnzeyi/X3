<?php
session_start();

// 1. Reba niba umuntu yinjiye (Session check)
if (isset($_SESSION['user_id'])) {

    // 2. Database Connection
    $host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "chatting";
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

    // Genzura niba connection ikora
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 3. Fata amakuru avuye muri chatroom.php
    $sender_id = $_SESSION['user_id'];
    $receiver_id = mysqli_real_escape_string($conn, $_POST['receiver_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // --- AGASHYA: Fata ID y'ubutumwa wasubije (Reply) ---
    // Niba ari ubutumwa busanzwe, biba ari NULL muri Database
    $reply_to = !empty($_POST['reply_to']) ? (int) $_POST['reply_to'] : "NULL";

    // 4. Reba niba ubutumwa butarimo ubusa
    if (!empty($message)) {

        // SQL 1: Injiza ubutumwa muri table ya messages (N'akantu ka reply_to)
        // Icyitonderwa: $reply_to ntiyashyirwa mu twitero ('') kuko ishobora kuba ari ijambo NULL
        $sql = "INSERT INTO messages (sender_id, receiver_id, msg_text, reply_to) 
                VALUES ({$sender_id}, {$receiver_id}, '{$message}', {$reply_to})";

        if (mysqli_query($conn, $sql)) {

            // SQL 2: Injiza notification kugira ngo undi muntu abone badge itukura
            $notif_sql = "INSERT INTO notifications (receiver_id, sender_id, type, is_read) 
                          VALUES ({$receiver_id}, {$sender_id}, 'message', 0)";

            mysqli_query($conn, $notif_sql);

            echo "Success";
        } else {
            echo "Error in message: " . mysqli_error($conn);
        }
    }
} else {
    // Niba adafite session, musubize kuri login page
    header("location: index.php");
}
?>