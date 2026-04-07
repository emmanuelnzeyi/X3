<?php
session_start();

// 1. SECURITY
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. FETCH LOGGED-IN USER DATA
$user_id = $_SESSION['user_id'];
$user_res = $conn->query("SELECT * FROM clients WHERE id = $user_id");
$user_data = $user_res->fetch_assoc();
$my_profile_img = !empty($user_data['picture']) ? $user_data['picture'] : 'https://ui-avatars.com/api/?name=' . $user_data['username'] . '&background=00dbde&color=fff';

// --- CHECK FOR UNREAD NOTIFICATIONS ---
$notif_check = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE receiver_id = $user_id AND is_read = 0");
$notif_row = $notif_check->fetch_assoc();
$has_unread = ($notif_row['unread'] > 0);

// --- FETCH UNREAD MESSAGES ---
$msg_check = $conn->query("SELECT COUNT(*) as unread_msg FROM messages WHERE receiver_id = $user_id AND status = 0");
$msg_row = $msg_check->fetch_assoc();
$unread_msg_count = $msg_row['unread_msg'];

// 4. FETCH ALL POSTS
$posts_query = "SELECT posts.*, clients.username, clients.picture 
                FROM posts 
                JOIN clients ON posts.user_id = clients.id 
                ORDER BY posts.post_id DESC";
$all_posts = $conn->query($posts_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Tolk | Feed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #000;
            color: #fff;
            font-family: -apple-system, sans-serif;
            overflow-x: hidden;
        }

        header {
            height: 60px;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            position: sticky;
            top: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(15px);
            z-index: 1000;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(45deg, #00dbde, #fc00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        #topDropdown {
            display: none;
            position: fixed;
            top: 55px;
            right: 10px;
            width: 200px;
            background: #121212;
            border: 1px solid #333;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            z-index: 3001;
            overflow: hidden;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #1a1a1a;
        }

        .dropdown-item i {
            width: 25px;
            font-size: 16px;
            margin-right: 10px;
            color: #00dbde;
        }

        #dropdownOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3000;
        }

        main {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 80px;
        }

        .feed-container {
            width: 100%;
            max-width: 500px;
            padding: 10px;
        }

        .post {
            background: #000;
            border: 1px solid #1a1a1a;
            margin-bottom: 25px;
            border-radius: 12px;
            overflow: hidden;
        }

        .post-header {
            display: flex;
            align-items: center;
            padding: 12px;
        }

        .post-header img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
            border: 1px solid #222;
        }

        .post-content {
            width: 100%;
            background: #050505;
        }

        .post-content img,
        .post-content video {
            width: 100%;
            display: block;
            max-height: 550px;
            object-fit: contain;
        }

        .post-actions {
            padding: 12px 14px;
            display: flex;
            gap: 20px;
            font-size: 24px;
        }

        .post-actions i {
            cursor: pointer;
            transition: 0.2s;
        }

        .post-actions .fa-heart.active {
            color: #ff4d4d;
        }

        .post-likes {
            padding: 0 14px;
            font-size: 13px;
            font-weight: 700;
            color: #00dbde;
        }

        .post-caption {
            padding: 8px 14px 5px;
            font-size: 14px;
        }

        .comments-display {
            padding: 5px 14px 15px;
            font-size: 13px;
            border-top: 1px solid #050505;
        }

        .comment-item {
            margin-top: 4px;
        }

        .comment-user {
            color: #00dbde;
            font-weight: 700;
            margin-right: 5px;
        }

        .view-all-btn {
            color: #888;
            text-decoration: none;
            font-size: 12px;
            margin-bottom: 8px;
            display: block;
            transition: 0.2s;
            cursor: pointer;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 65px;
            background: #000;
            border-top: 1px solid #1a1a1a;
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }

        .nav-item {
            color: #fff;
            font-size: 22px;
            text-decoration: none;
            position: relative;
        }

        .nav-item.profile img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #fff;
        }

        .notif-dot {
            position: absolute;
            top: -2px;
            right: -3px;
            background: #ff4d4d;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid #000;
            display:
                <?php echo $has_unread ? 'block' : 'none'; ?>
            ;
        }

        .msg-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #ff4d4d;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 10px;
            font-weight: bold;
            border: 2px solid #000;
            display:
                <?php echo ($unread_msg_count > 0) ? 'block' : 'none'; ?>
            ;
        }

        #commentModal,
        #shareModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #111;
            width: 90%;
            max-width: 400px;
            padding: 20px;
            border-radius: 15px;
            border: 1px solid #333;
        }

        #all_comments_list,
        #friends_list {
            max-height: 250px;
            overflow-y: auto;
            margin: 15px 0;
            border-bottom: 1px solid #222;
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    <header>
        <a href="home.php" class="brand-logo">BILL TOLK</a>
        <i class="fa-solid fa-bars" onclick="toggleMenu()" style="font-size: 22px; cursor: pointer; color: #fff;"></i>
    </header>

    <div id="dropdownOverlay" onclick="toggleMenu()"></div>
    <div id="topDropdown">
        <a href="settings.php" class="dropdown-item"><i class="fa-solid fa-gear"></i> Settings</a>
        <a href="saved.php" class="dropdown-item"><i class="fa-regular fa-bookmark"></i> Saved</a>
        <a href="activity.php" class="dropdown-item"><i class="fa-solid fa-clock-rotate-left"></i> Your Activity</a>
        <hr style="border: 0; border-top: 1px solid #222; margin: 0;">
        <a href="logout.php" class="dropdown-item" style="color: #ff4d4d;"><i
                class="fa-solid fa-right-from-bracket"></i> Log Out</a>
    </div>

    <main>
        <div class="feed-container">
            <?php while ($post = $all_posts->fetch_assoc()):
                $p_id = $post['post_id'];
                $file_path = $post['post_image'];
                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $is_video = in_array($ext, ['mp4', 'webm', 'mov', 'ogg']);
                $poster_img = !empty($post['picture']) ? $post['picture'] : 'https://ui-avatars.com/api/?name=' . $post['username'];
                $check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND post_id = $p_id");
                $heart_class = ($check_like && $check_like->num_rows > 0) ? "fa-solid active" : "fa-regular";
                $check_saved = $conn->query("SELECT * FROM saved_posts WHERE user_id = $user_id AND post_id = $p_id");
                $save_icon_class = ($check_saved && $check_saved->num_rows > 0) ? "fa-solid" : "fa-regular";
                ?>
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo $poster_img; ?>">
                        <div class="username"><?php echo htmlspecialchars($post['username']); ?></div>
                    </div>
                    <div class="post-content">
                        <?php if ($is_video): ?>
                            <video src="<?php echo $file_path; ?>" class="post-video" loop muted playsinline controls></video>
                        <?php else: ?>
                            <img src="<?php echo $file_path; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="post-actions">
                        <i class="<?php echo $heart_class; ?> fa-heart like-btn" data-id="<?php echo $p_id; ?>"
                            id="btn-<?php echo $p_id; ?>"></i>
                        <i class="fa-regular fa-comment" onclick="openCommentModal('<?php echo $p_id; ?>')"></i>
                        <i class="<?php echo $save_icon_class; ?> fa-bookmark"
                            onclick="toggleSave(this, <?php echo $p_id; ?>)"></i>
                        <i class="fa-regular fa-paper-plane" onclick="openShareModal('<?php echo $p_id; ?>')"></i>
                    </div>
                    <div class="post-likes"><span
                            id="likes-<?php echo $p_id; ?>"><?php echo number_format($post['likes_count']); ?></span> likes
                    </div>
                    <div class="post-caption"><b><?php echo $post['username']; ?></b>
                        <?php echo htmlspecialchars($post['caption']); ?></div>
                    <div class="comments-display">
                        <?php
                        $count_res = $conn->query("SELECT COUNT(*) as total FROM comments WHERE post_id = $p_id");
                        $total_comments = $count_res->fetch_assoc()['total'];
                        $comments = $conn->query("SELECT comments.*, clients.username FROM comments JOIN clients ON comments.user_id = clients.id WHERE post_id = $p_id ORDER BY id ASC LIMIT 3");
                        if ($total_comments > 3): ?>
                            <div class="view-all-btn" onclick="openCommentModal('<?php echo $p_id; ?>')">View all
                                <?php echo $total_comments; ?> comments
                            </div>
                        <?php endif; ?>
                        <?php while ($c = $comments->fetch_assoc()): ?>
                            <div class="comment-item"><span
                                    class="comment-user"><?php echo htmlspecialchars($c['username']); ?></span><span
                                    style="color:#ccc;"><?php echo htmlspecialchars($c['comment_text']); ?></span></div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <nav class="bottom-nav">
        <a href="home.php" class="nav-item"><i class="fa-solid fa-house"></i></a>
        <a href="search.php" class="nav-item"><i class="fa-solid fa-magnifying-glass"></i></a>
        <a href="chatroom.php" class="nav-item" onclick="clearMessageBadge()">
            <i class="fa-brands fa-facebook-messenger" style="color:#00dbde; font-size:26px;"></i>
            <span class="msg-badge"><?php echo $unread_msg_count; ?></span>
        </a>
        <a href="reels.php" class="nav-item"><i class="fa-solid fa-clapperboard"></i></a>
        <a href="notifications.php" class="nav-item"><i class="fa-regular fa-bell"></i><span
                class="notif-dot"></span></a>
        <a href="profile.php" class="nav-item profile"><img src="<?php echo $my_profile_img; ?>"></a>
    </nav>

    <div id="commentModal">
        <div class="modal-content">
            <h3 style="margin:0; color:#00dbde;">Comments</h3>
            <div id="all_comments_list"></div>
            <input type="hidden" id="modal_post_id">
            <textarea id="comment_text" rows="3" placeholder="Add a comment..."
                style="width:100%; background:#222; color:#fff; border:1px solid #333; border-radius:10px; padding:10px; outline:none; font-family:inherit;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                <button onclick="closeCommentModal()"
                    style="background:none; border:none; color:#777; cursor:pointer;">Close</button>
                <button onclick="submitComment()"
                    style="background:#fc00ff; border:none; color:#fff; padding:8px 20px; border-radius:20px; cursor:pointer; font-weight:bold;">Post</button>
            </div>
        </div>
    </div>

    <div id="shareModal">
        <div class="modal-content" style="max-width:350px;">
            <h3 style="margin:0; color:#00dbde; text-align:center;">Send to</h3>
            <div id="friends_list"></div>
            <input type="hidden" id="share_post_id">
            <button onclick="closeShareModal()"
                style="width:100%; background:#333; color:#fff; border:none; padding:10px; border-radius:10px; cursor:pointer; margin-top:10px;">Cancel</button>
        </div>
    </div>

    <script>
        // --- NEW: AUTO-PLAY VIDEO LOGIC ---
        const allVideos = document.querySelectorAll('.post-video');
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.play().catch(e => console.log("Autoplay blocked"));
                } else {
                    entry.target.pause();
                }
            });
        }, { threshold: 0.5 });

        allVideos.forEach(video => {
            videoObserver.observe(video);
            // Tap to unmute/pause logic
            video.addEventListener('click', () => {
                video.muted = !video.muted;
            });
        });

        // --- PRE-EXISTING SCRIPTS ---
        function clearMessageBadge() {
            const badge = document.querySelector('.msg-badge');
            if (badge) badge.style.display = 'none';
            fetch('mark_messages_read.php').then(res => res.text()).catch(err => console.error(err));
        }

        function updateMessageBadge() {
            fetch('check_notifications.php')
                .then(res => res.text())
                .then(count => {
                    const badge = document.querySelector('.msg-badge');
                    const countInt = parseInt(count.trim());
                    if (countInt > 0) {
                        badge.innerText = countInt;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                });
        }
        setInterval(updateMessageBadge, 3000);

        function toggleMenu() {
            const dropdown = document.getElementById('topDropdown');
            const overlay = document.getElementById('dropdownOverlay');
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
            overlay.style.display = (overlay.style.display === 'block') ? 'none' : 'block';
        }

        function toggleSave(element, postId) {
            fetch('save_process.php?post_id=' + postId)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'saved') {
                        element.classList.replace('fa-regular', 'fa-solid');
                    } else {
                        element.classList.replace('fa-solid', 'fa-regular');
                    }
                });
        }

        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;
                fetch('like_process.php?post_id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.classList.toggle('fa-solid');
                            this.classList.toggle('fa-regular');
                            this.classList.toggle('active');
                            document.getElementById('likes-' + id).innerText = data.new_count;
                        }
                    });
            });
        });

        function openCommentModal(id) {
            document.getElementById('modal_post_id').value = id;
            document.getElementById('all_comments_list').innerHTML = '<p style="color: #666; font-size: 13px;">Loading...</p>';
            document.getElementById('commentModal').style.display = 'flex';
            fetch('fetch_comments.php?post_id=' + id).then(res => res.text()).then(data => { document.getElementById('all_comments_list').innerHTML = data; });
        }
        function closeCommentModal() { document.getElementById('commentModal').style.display = 'none'; }
        function submitComment() {
            const postId = document.getElementById('modal_post_id').value;
            const commentText = document.getElementById('comment_text').value;
            if (commentText.trim() === "") return;
            const params = new URLSearchParams();
            params.append('post_id', postId);
            params.append('comment', commentText);
            fetch('comment_process.php', { method: 'POST', body: params }).then(res => res.json()).then(data => { if (data.status === 'success') { location.reload(); } });
        }

        function openShareModal(id) {
            document.getElementById('share_post_id').value = id;
            document.getElementById('friends_list').innerHTML = '<p style="color: #666; text-align:center;">Loading...</p>';
            document.getElementById('shareModal').style.display = 'flex';
            fetch('fetch_friends_to_share.php').then(res => res.text()).then(data => { document.getElementById('friends_list').innerHTML = data; });
        }
        function closeShareModal() { document.getElementById('shareModal').style.display = 'none'; }

        function sendToFriend(friendId) {
            const postId = document.getElementById('share_post_id').value;
            const params = new URLSearchParams();
            params.append('receiver_id', friendId);
            params.append('post_id', postId);
            fetch('share_to_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert("Sent!");
                        closeShareModal();
                    }
                });
        }
    </script>
</body>

</html>