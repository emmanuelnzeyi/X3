<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "chatting");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    exit("Login required");
}
$user_id = $_SESSION['user_id'];

// Fata abantu bose muri table ya 'clients'
$sql = "SELECT id, username, picture FROM clients WHERE id != $user_id ORDER BY username ASC";
$query = $conn->query($sql);

if ($query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        // Ifoto y'uwitwa Default niba nta yihariye afite
        $img = !empty($row['picture']) ? $row['picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($row['username']) . '&background=00dbde&color=000';

        echo '
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #1a1a1a;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="' . $img . '" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #333;">
                <span style="color: #fff; font-size: 14px;">' . htmlspecialchars($row['username']) . '</span>
            </div>
            <button onclick="sendToFriend(' . $row['id'] . ')" 
                    id="btn-share-' . $row['id'] . '"
                    style="background: #00dbde; border: none; color: #000; padding: 6px 12px; border-radius: 15px; cursor: pointer; font-size: 11px; font-weight: bold;">
                Send
            </button>
        </div>';
    }
} else {
    echo '<div style="text-align: center; padding: 20px; color: #666;">No friends found.</div>';
}
?>