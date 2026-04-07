<?php
session_start();

// 1. Reba niba umuntu yinjiye niba n'amakuru aje (msg_id na emoji)
if (isset($_SESSION['user_id']) && isset($_POST['msg_id']) && isset($_POST['emoji'])) {

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
    $emoji = mysqli_real_escape_string($conn, $_POST['emoji']);

    // 3. LOGIC: Reba niba uyu muntu asanzwe yarashyize reaction kuri iyi message
    // Ibi bituma iyo ukanze ❤️ hanyuma ugakanda 😂, iya mbere ivaho hagajya nshya (nk'ukuri Instagram)
    $check_q = "SELECT * FROM reactions WHERE message_id = $msg_id AND user_id = $user_id";
    $check_res = $conn->query($check_q);

    if ($check_res->num_rows > 0) {
        // Niba asanzwe ayifite, vugurura (Update) iyo asanzwe afite
        $sql = "UPDATE reactions SET emoji = '$emoji' 
                WHERE message_id = $msg_id AND user_id = $user_id";
    } else {
        // Niba ari nshya, yinjize (Insert) muri database
        $sql = "INSERT INTO reactions (message_id, user_id, emoji) 
                VALUES ($msg_id, $user_id, '$emoji')";
    }

    if ($conn->query($sql)) {
        echo "Reaction saved";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>