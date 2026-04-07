<?php
session_start();

// 1. Database Connection
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "chatting";
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Reba niba umuntu yinjiye
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fata uwo muntu uri kuvugana nawe ubu (kugira ngo Sidebar imugaragaze nka Active)
    $chat_with = isset($_POST['chat_with']) ? (int) $_POST['chat_with'] : 0;

    // 3. Soma abantu bose usibye wowe
    $all_users = $conn->query("SELECT id, username, picture FROM clients WHERE id != $user_id ORDER BY id DESC");
    $output = "";

    if ($all_users->num_rows > 0) {
        while ($row = $all_users->fetch_assoc()) {
            $u_id = $row['id'];

            // 4. BARURA NOTIFICATIONS (Ubutumwa uyu muntu yakuherereje butarasomwa)
            $notif_q = "SELECT COUNT(*) as unread FROM notifications 
                        WHERE receiver_id = $user_id AND sender_id = $u_id AND is_read = 0";
            $notif_res = $conn->query($notif_q);
            $unread_count = $notif_res->fetch_assoc()['unread'];

            // Tegura akamenyetso gatukura (Badge)
            $badge = ($unread_count > 0) ? "<span class='unread-badge' style='background: #ff0000; color: #fff; padding: 2px 8px; border-radius: 50%; font-size: 11px; font-weight: bold; margin-left: auto;'>$unread_count</span>" : "";

            // Ifoto y'umuntu
            $u_pic = !empty($row['picture']) ? $row['picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['username']);

            // Reba niba ari uyu muntu ukanzeho (Active class)
            $active = ($chat_with == $u_id) ? 'active' : '';

            // 5. HTML izasohoka muri Sidebar
            $output .= "<a href='chatroom.php?chat_with={$u_id}' class='user-item {$active}' style='display: flex; align-items: center; padding: 15px 10px; text-decoration: none; color: #fff; border-bottom: 1px solid #111;'>
                            <img src='{$u_pic}' style='width: 45px; height: 45px; border-radius: 50%; margin-right: 12px; object-fit: cover;'>
                            <div style='flex: 1;'>
                                <div style='font-weight: bold;'>" . htmlspecialchars($row['username']) . "</div>
                                <small style='color: #888;'>Online</small>
                            </div>
                            $badge
                        </a>";
        }
    } else {
        $output = "<div style='padding: 20px; color: #555; text-align: center;'>Nta bakoresha bahari.</div>";
    }
    echo $output;
}
?>