<?php
session_start();

// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SECURITY: Reba niba umuntu yinjiye
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. HAMAGARA NOTIFICATIONS
// JOIN kugira ngo tubone amazina n'amafoto by'abakoze action (sender)
$sql = "SELECT n.*, c.username, c.picture 
        FROM notifications n 
        JOIN clients c ON n.sender_id = c.id 
        WHERE n.receiver_id = $user_id 
        ORDER BY n.created_at DESC LIMIT 30";
$result = $conn->query($sql);

// 3. UPDATE IS_READ STATUS
// Iyo umuntu ageze hano, notifications zose ziba zisomwe (Dot igahita izima kuri Home)
$update_sql = "UPDATE notifications SET is_read = 1 WHERE receiver_id = $user_id AND is_read = 0";
$conn->query($update_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Bill Tolk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, sans-serif;
            margin: 0;
        }

        header {
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 15px;
            border-bottom: 1px solid #1a1a1a;
            position: sticky;
            top: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 100;
        }

        .back-btn {
            color: #fff;
            font-size: 22px;
            margin-right: 15px;
            text-decoration: none;
        }

        h2 {
            font-size: 18px;
            margin: 0;
            font-weight: 800;
            color: #00dbde;
            text-transform: uppercase;
        }

        .notif-list {
            padding-bottom: 20px;
        }

        .notif-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            text-decoration: none;
            color: #fff;
            border-bottom: 1px solid #0a0a0a;
            transition: 0.2s;
        }

        /* Style ya notification itarasomwa */
        .notif-item.unread {
            background: rgba(0, 219, 222, 0.05);
            border-left: 3px solid #00dbde;
        }

        .notif-item:active {
            background: #111;
        }

        .notif-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #222;
        }

        .notif-content {
            flex: 1;
            font-size: 14px;
            line-height: 1.4;
        }

        .notif-content b {
            color: #00dbde;
        }

        /* Style y'amagambo ya comment muri notification */
        .comment-msg {
            color: #bbb;
            font-style: italic;
            display: block;
            margin-top: 2px;
            font-size: 13px;
        }

        .notif-time {
            font-size: 11px;
            color: #555;
            display: block;
            margin-top: 4px;
        }

        .empty-state {
            text-align: center;
            margin-top: 150px;
            color: #444;
        }

        .empty-state i {
            margin-bottom: 15px;
            color: #1a1a1a;
        }
    </style>
</head>

<body>

    <header>
        <a href="home.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        <h2>Notifications</h2>
    </header>

    <div class="notif-list">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sender_name = htmlspecialchars($row['username']);
                $sender_pic = !empty($row['picture']) ? $row['picture'] : 'https://ui-avatars.com/api/?name=' . $sender_name . '&background=00dbde&color=fff';

                // Igihe: Today cyangwa Date isanzwe
                if (date('Y-m-d', strtotime($row['created_at'])) == date('Y-m-d')) {
                    $time_display = "Today at " . date("H:i", strtotime($row['created_at']));
                } else {
                    $time_display = date("M d, H:i", strtotime($row['created_at']));
                }

                // --- LOGIC YO GUSOMA TYPE ---
                $raw_type = $row['type'];
                $action_text = "";
                $is_comment = false;

                if (strpos($raw_type, 'comment:') === 0) {
                    $is_comment = true;
                    $comment_body = str_replace('comment: ', '', $raw_type);
                    $action_text = "commented on your post:";
                } else {
                    switch ($raw_type) {
                        case 'like':
                            $action_text = "liked your post.";
                            break;
                        case 'follow':
                            $action_text = "started following you.";
                            break;
                        case 'message':
                            $action_text = "sent you a message.";
                            break;
                        default:
                            $action_text = "interacted with your post.";
                    }
                }

                $link = ($row['post_id'] > 0) ? "home.php#post-" . $row['post_id'] : "#";
                ?>

                <a href="<?php echo $link; ?>" class="notif-item <?php echo ($row['is_read'] == 0) ? 'unread' : ''; ?>">
                    <img src="<?php echo $sender_pic; ?>" alt="profile">
                    <div class="notif-content">
                        <b><?php echo $sender_name; ?></b> <?php echo $action_text; ?>

                        <?php if ($is_comment): ?>
                            <span class="comment-msg">"<?php echo htmlspecialchars($comment_body); ?>..."</span>
                        <?php endif; ?>

                        <span class="notif-time"><?php echo $time_display; ?></span>
                    </div>
                </a>

                <?php
            }
        } else {
            echo '<div class="empty-state">
                    <i class="fa-regular fa-bell-slash fa-4x"></i>
                    <p>No notifications yet</p>
                  </div>';
        }
        ?>
    </div>

</body>

</html>