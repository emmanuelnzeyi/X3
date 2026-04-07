<?php
// 1. DATABASE CONNECTION
// Reba niba amanyina ya database (chatting) n'ibindi bihuye n'ibyo ukoresha
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. REBA NIBA POST_ID YAGEZEMO (Yatanzwe na JavaScript muri home.php)
if (isset($_GET['post_id'])) {
    $p_id = intval($_GET['post_id']);

    // 3. HAMAGARA COMMENTS ZOSE KURI IYO POST
    // Turazijyana dufatanyije (JOIN) n'izina ry'uwazanditse (clients)
    $query = "SELECT comments.*, clients.username 
              FROM comments 
              JOIN clients ON comments.user_id = clients.id 
              WHERE post_id = $p_id 
              ORDER BY comments.id DESC"; // DESC ituma inshya ziza hejuru

    $res = $conn->query($query);

    if ($res && $res->num_rows > 0) {
        // Herekana buri comment mu buryo bwiza (HTML)
        while ($c = $res->fetch_assoc()) {
            echo '<div style="margin-bottom: 12px; border-bottom: 1px solid #1a1a1a; padding-bottom: 8px;">';
            echo '<span style="color: #00dbde; font-weight: 800; margin-right: 8px; font-size: 14px;">' . htmlspecialchars($c['username']) . ':</span>';
            echo '<span style="color: #fff; font-size: 13px; line-height: 1.4;">' . htmlspecialchars($c['comment_text']) . '</span>';
            echo '</div>';
        }
    } else {
        // Niba nta comments zihari
        echo '<p style="color: #666; font-size: 13px; text-align: center; margin-top: 20px;">No comments yet. Be the first to comment!</p>';
    }
} else {
    echo "No post selected.";
}

$conn->close();
?>