<?php
session_start();
// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

// Genzura niba user yinjiye (Security)
if (!isset($_SESSION['user_id'])) {
    exit();
}

if ($conn->connect_error) {
    exit("Connection failed");
}

$user_id = $_SESSION['user_id'];

// 2. GET SEARCH TERM (Iva kuri AJAX muri search.php)
$searchTerm = isset($_POST['searchTerm']) ? mysqli_real_escape_string($conn, $_POST['searchTerm']) : '';

if ($searchTerm == "") {
    exit();
}

// 3. QUERY DATABASE (Shaka amazina asa n'iryo yanditse, ariko ukuremo uwinjiye)
$sql = "SELECT * FROM clients WHERE (username LIKE '%{$searchTerm}%') AND id != {$user_id} LIMIT 10";
$query = $conn->query($sql);
$output = "";

if ($query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        $u_name = htmlspecialchars($row['username']);
        $u_id = $row['id'];

        // Niba nta foto afite, koresha UI Avatars ifite ibara rya Bill Tolk (#00dbde)
        $u_pic = !empty($row['picture']) ? $row['picture'] : 'https://ui-avatars.com/api/?name=' . $u_name . '&background=00dbde&color=fff';

        // IYI LINK `href="profile.php?user_id=..."` NIYO ITUMA UKANDAHO UKAJYA KURI PROFILE
        $output .= '
            <a href="profile.php?user_id=' . $u_id . '" class="user-item" style="display: flex; align-items: center; gap: 15px; padding: 12px 15px; text-decoration: none; border-bottom: 1px solid #111; transition: 0.2s;">
                <img src="' . $u_pic . '" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 1.5px solid #222;">
                <div class="user-info" style="flex: 1;">
                    <h4 style="margin: 0; font-size: 15px; color: #fff; font-weight: 600;">' . $u_name . '</h4>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #777;">View Profile</p>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: #333; font-size: 12px;"></i>
            </a>';
    }
} else {
    // Niba nta muntu ubonetse mu 'Kingdom'
    $output = '
        <div class="placeholder-text" style="text-align: center; padding: 30px; color: #555; font-size: 14px;">
            <i class="fa-solid fa-face-frown fa-2x" style="display: block; margin-bottom: 10px;"></i>
            No users found matching "' . htmlspecialchars($searchTerm) . '"
        </div>';
}

echo $output;
?>