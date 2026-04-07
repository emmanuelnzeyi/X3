<?php
session_start();
// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. SECURITY: Redirect niba adasigaye logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$my_id = $_SESSION['user_id'];

// --- MENYA UWO URI KUREBA ---
$view_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $my_id;
$is_mine = ($my_id === $view_user_id);

// --- 3. LOGIC: GUHINDURA PROFILE PICTURE ---
if ($is_mine && isset($_POST['update_profile_pic'])) {
    if (!empty($_FILES['new_picture']['name'])) {
        $check_old = $conn->query("SELECT picture FROM clients WHERE id = $my_id");
        $old_data = $check_old->fetch_assoc();
        $old_pic = $old_data['picture'];

        $target_dir = "uploads/profiles/";
        if (!is_dir($target_dir))
            mkdir($target_dir, 0777, true);

        $file_name = time() . "_profile_" . basename($_FILES["new_picture"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["new_picture"]["tmp_name"], $target_file)) {
            if (!empty($old_pic) && file_exists($old_pic)) {
                unlink($old_pic);
            }
            $conn->query("UPDATE clients SET picture = '$target_file' WHERE id = $my_id");
            header("Location: profile.php");
            exit();
        }
    }
}

// --- 4. LOGIC: GUSHYIRAHO POST ---
if ($is_mine && isset($_POST['create_post'])) {
    $caption = $conn->real_escape_string($_POST['caption']);
    if (!empty($_FILES['post_file']['name'])) {
        $post_dir = "uploads/posts/";
        if (!is_dir($post_dir))
            mkdir($post_dir, 0777, true);

        $file_name = $_FILES['post_file']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $target_post = $post_dir . time() . "_post_" . basename($file_name);

        if (move_uploaded_file($_FILES["post_file"]["tmp_name"], $target_post)) {
            $video_extensions = ['mp4', 'webm', 'mov', 'avi', 'ogg'];
            $is_reel = in_array($ext, $video_extensions) ? 1 : 0;

            $sql = "INSERT INTO posts (user_id, post_image, caption, is_reel) 
                    VALUES ($my_id, '$target_post', '$caption', $is_reel)";
            $conn->query($sql);
            header("Location: profile.php");
            exit();
        }
    }
}

// 5. SOMA AMAKURU Y'UMUNTU URI KUREBA
$user_res = $conn->query("SELECT * FROM clients WHERE id = $view_user_id");
$user = $user_res->fetch_assoc();
$username = htmlspecialchars($user['username']);
$picture = !empty($user['picture']) ? $user['picture'] : 'https://ui-avatars.com/api/?name=' . $username . '&background=00dbde&color=000';

// 6. SOMA POSTS
$my_posts = $conn->query("SELECT * FROM posts WHERE user_id = $view_user_id ORDER BY post_id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $username; ?> | Bill Tolk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, sans-serif;
            margin: 0;
            padding-bottom: 60px;
        }

        .nav-header {
            position: fixed;
            top: 0;
            width: 100%;
            height: 60px;
            background: #000;
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid #111;
            z-index: 100;
            box-sizing: border-box;
        }

        .profile-info {
            text-align: center;
            margin-top: 80px;
            padding: 20px;
        }

        .pic-box {
            position: relative;
            width: 110px;
            height: 110px;
            margin: 0 auto;
            <?php echo $is_mine ? 'cursor: pointer;' : ''; ?>
        }

        .pic-box img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #00dbde;
        }

        .pic-box .cam-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #00dbde;
            color: #000;
            border-radius: 50%;
            padding: 6px;
            font-size: 12px;
        }

        .btn-message {
            background: #00dbde;
            color: #000;
            padding: 10px 35px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn-message:active {
            transform: scale(0.95);
        }

        .create-post-bar {
            background: #050505;
            margin: 20px;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #1a1a1a;
            text-align: center;
            cursor: pointer;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            padding: 2px;
        }

        .grid-item {
            aspect-ratio: 1/1;
            background: #111;
            overflow: hidden;
            position: relative;
        }

        .grid-item img,
        .grid-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .grid-item i.fa-clapperboard {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 14px;
            color: #fff;
        }

        /* Save Icon style gusa niyo nshyizemo */
        .save-btn {
            position: absolute;
            bottom: 8px;
            right: 8px;
            z-index: 10;
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #111;
            padding: 25px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            border: 1px solid #222;
        }

        .modal-content input,
        .modal-content textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: #000;
            border: 1px solid #333;
            color: #fff;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .btn-action {
            background: #00dbde;
            color: #000;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-cancel {
            text-align: center;
            color: #ff4d4d;
            cursor: pointer;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="nav-header">
        <a href="home.php" style="color:#fff;"><i class="fa-solid fa-arrow-left fa-lg"></i></a>
        <div style="margin-left: 20px; font-weight: bold;"><?php echo $username; ?></div>
    </div>

    <div class="profile-info">
        <div class="pic-box" <?php if ($is_mine)
            echo 'onclick="toggleModal(\'profileModal\')"'; ?>>
            <img src="<?php echo $picture; ?>">
            <?php if ($is_mine): ?>
                <div class="cam-icon"><i class="fa-solid fa-camera"></i></div>
            <?php endif; ?>
        </div>
        <h3 style="margin-top:15px;"><?php echo $username; ?></h3>
        <?php if (!$is_mine): ?>
            <a href="chatroom.php?chat_with=<?php echo $view_user_id; ?>" class="btn-message">
                <i class="fa-solid fa-paper-plane"></i> Message
            </a>
        <?php endif; ?>
    </div>

    <?php if ($is_mine): ?>
        <div class="create-post-bar" onclick="toggleModal('postModal')">
            <i class="fa-regular fa-square-plus" style="color: #00dbde; font-size: 24px;"></i>
            <p style="margin: 5px 0 0; font-size: 13px;">Create New Post / Reel</p>
        </div>
    <?php endif; ?>

    <div class="grid-container">
        <?php if ($my_posts->num_rows > 0): ?>
            <?php while ($p = $my_posts->fetch_assoc()):
                $file_path = $p['post_image'];
                $post_id = $p['post_id'];
                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $is_video = in_array($ext, ['mp4', 'webm', 'mov', 'ogg']);

                // Genzura niba yarayisavinze
                $check_saved = $conn->query("SELECT id FROM saved_posts WHERE user_id = $my_id AND post_id = $post_id");
                $is_saved = ($check_saved->num_rows > 0);
                ?>
                <div class="grid-item">
                    <?php if ($is_video): ?>
                        <i class="fa-solid fa-clapperboard"></i>
                        <video src="<?php echo $file_path; ?>" muted loop onmouseover="this.play()"
                            onmouseout="this.pause()"></video>
                    <?php else: ?>
                        <img src="<?php echo $file_path; ?>">
                    <?php endif; ?>

                    <div class="save-btn" onclick="saveVideo(this, <?php echo $post_id; ?>)">
                        <i class="<?php echo $is_saved ? 'fa-solid' : 'fa-regular'; ?> fa-bookmark"
                            style="color: <?php echo $is_saved ? '#00dbde' : '#fff'; ?>;"></i>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: span 3; text-align: center; padding: 60px 20px; color: #555;">
                <i class="fa-solid fa-camera-retro fa-3x"></i>
                <p>No posts yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($is_mine): ?>
        <div id="profileModal" class="modal">
            <div class="modal-content">
                <h3>Update Profile Photo</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="new_picture" accept="image/*" required>
                    <button type="submit" name="update_profile_pic" class="btn-action">Upload & Replace</button>
                    <div class="btn-cancel" onclick="toggleModal('profileModal')">Cancel</div>
                </form>
            </div>
        </div>

        <div id="postModal" class="modal">
            <div class="modal-content">
                <h3>New Post / Reel</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="post_file" accept="image/*,video/*" required>
                    <textarea name="caption" placeholder="Write a caption..." rows="3"></textarea>
                    <button type="submit" name="create_post" class="btn-action">Share Now</button>
                    <div class="btn-cancel" onclick="toggleModal('postModal')">Cancel</div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script>
        function toggleModal(id) {
            const m = document.getElementById(id);
            if (m) m.style.display = (m.style.display === 'flex') ? 'none' : 'flex';
        }

        // Save Function yongerewe hano
        function saveVideo(element, postId) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "save_action.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (this.status == 200) {
                    let res = this.responseText.trim();
                    let icon = element.querySelector('i');
                    if (res == "saved") {
                        icon.classList.replace('fa-regular', 'fa-solid');
                        icon.style.color = "#00dbde";
                    } else {
                        icon.classList.replace('fa-solid', 'fa-regular');
                        icon.style.color = "#fff";
                    }
                }
            }
            xhr.send("post_id=" + postId);
        }

        window.onclick = function (event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>