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

if (isset($_SESSION['user_id'])) {
    $sender_id = (int) $_SESSION['user_id'];
    $receiver_id = isset($_POST['receiver_id']) ? (int) $_POST['receiver_id'] : 0;

    if ($receiver_id > 0) {
        // Update notifications
        $update_notif = "UPDATE notifications SET is_read = 1 
                         WHERE receiver_id = $sender_id AND sender_id = $receiver_id";
        $conn->query($update_notif);

        // FETCH MESSAGES WITH REPLIED TEXT
        $sql = "SELECT m.*, 
                (SELECT msg_text FROM messages WHERE msg_id = m.reply_to) as replied_text
                FROM messages m 
                WHERE (sender_id = $sender_id AND receiver_id = $receiver_id) 
                OR (sender_id = $receiver_id AND receiver_id = $sender_id) 
                ORDER BY msg_id ASC";

        $query = $conn->query($sql);
        $output = "";

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $msg_id = $row['msg_id'];
                $msg = $row['msg_text'];
                $is_shared = (strpos($msg, 'shared_post:') === 0);
                $is_outgoing = ($row['sender_id'] == $sender_id);
                $content = "";

                // Tegura message yo kohereza muri JS (Copy/Forward)
                $msg_for_js = addslashes(htmlspecialchars($msg));

                // --- 1. HANDLING MEDIA ---
                if ($is_shared) {
                    $media_url = str_replace('shared_post:', '', $msg);
                    $full_url = (strpos($media_url, 'uploads/') === false && !filter_var($media_url, FILTER_VALIDATE_URL))
                        ? "uploads/" . $media_url : $media_url;

                    $ext = strtolower(pathinfo($full_url, PATHINFO_EXTENSION));
                    $is_vid = in_array($ext, ['mp4', 'webm', 'mov', 'ogg']);

                    $content = '<div style="margin-bottom: 5px; font-size: 10px; opacity: 0.8; font-weight: bold; color: inherit;">
                                    <i class="fa-solid fa-share-from-square"></i> SHARED POST
                                </div>';

                    if ($is_vid) {
                        $content .= '<video style="width: 100%; border-radius: 10px; max-height: 250px; background: #000;" controls preload="metadata">
                                        <source src="' . $full_url . '#t=0.1" type="video/mp4">
                                     </video>';
                    } else {
                        $content .= '<img src="' . $full_url . '" style="width: 100%; border-radius: 10px; max-height: 250px; object-fit: cover;">';
                    }
                } else {
                    $content = '<p style="margin: 0; font-size: 14.5px;">' . htmlspecialchars($msg) . '</p>';
                }

                // --- 2. FETCH REACTIONS ---
                $react_q = $conn->query("SELECT emoji FROM reactions WHERE message_id = $msg_id");
                $reaction_html = "";
                if ($react_q->num_rows > 0) {
                    $reaction_html = '<div style="position:absolute; bottom:-12px; ' . ($is_outgoing ? 'right:10px;' : 'left:10px;') . ' background:#111; border:1px solid #333; border-radius:10px; padding:0 5px; display:flex; gap:2px; z-index:5;">';
                    while ($re = $react_q->fetch_assoc()) {
                        $reaction_html .= '<span style="font-size:11px;">' . $re['emoji'] . '</span>';
                    }
                    $reaction_html .= '</div>';
                }

                // --- 3. OUTPUT THE BUBBLE ---
                $side_class = $is_outgoing ? "outgoing" : "incoming";
                $justify = $is_outgoing ? "flex-end" : "flex-start";
                $bg_color = $is_outgoing ? ($is_shared ? "#111" : "#00dbde") : "#1a1a1a";
                $text_color = $is_outgoing ? ($is_shared ? "#00dbde" : "#000") : "#fff";
                $radius = $is_outgoing ? "18px 18px 0 18px" : "18px 18px 18px 0";

                // Hano nongeyeho "oncontextmenu" kugira ngo Right-Click nayo ifungure menu ya Unsend
                $output .= '<div class="msg-wrapper ' . $side_class . '" 
                                oncontextmenu="showMoreOptions(' . $msg_id . ', ' . ($is_outgoing ? 'true' : 'false') . ', \'' . $msg_for_js . '\', event); return false;"
                                style="display: flex; justify-content: ' . $justify . '; margin-bottom: 20px; width: 100%; position:relative;">
                                
                                <div class="action-menu">
                                    <span onclick="react(' . $msg_id . ', \'❤️\')">❤️</span>
                                    <span onclick="react(' . $msg_id . ', \'😂\')">😂</span>
                                    <span onclick="react(' . $msg_id . ', \'🔥\')">🔥</span>
                                    <span onclick="setReply(' . $msg_id . ', \'' . $msg_for_js . '\')"><i class="fa-solid fa-reply"></i></span>
                                    
                                    <span onclick="showMoreOptions(' . $msg_id . ', ' . ($is_outgoing ? 'true' : 'false') . ', \'' . $msg_for_js . '\', event)">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </span>
                                </div>

                                <div class="details" style="max-width: 75%; position:relative;">';

                if (!empty($row['replied_text'])) {
                    $output .= '<div style="background: rgba(255,255,255,0.07); padding: 5px 10px; border-left: 3px solid #00dbde; font-size: 11px; color: #888; margin-bottom: -5px; border-radius: 8px 8px 0 0; opacity: 0.8;">
                                    <i class="fa-solid fa-reply" style="font-size:9px;"></i> ' . htmlspecialchars(substr($row['replied_text'], 0, 35)) . '...
                                </div>';
                }

                $output .= '        <div style="background: ' . $bg_color . '; color: ' . $text_color . '; padding: 10px 15px; border-radius: ' . $radius . '; border: 1px solid #333; word-wrap: break-word;">
                                        ' . $content . '
                                    </div>
                                    ' . $reaction_html . '
                                </div>
                            </div>';
            }
        } else {
            $output = '<div style="text-align:center; margin-top: 100px; color: #555;">
                            <i class="fa-solid fa-comments" style="font-size: 40px; margin-bottom: 10px; opacity: 0.2;"></i><br>
                            Nta butumwa buraboneka.
                       </div>';
        }
        echo $output;
    }
}
?>