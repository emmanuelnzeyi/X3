<?php
session_start();

// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. FETCH REELS (Zana video zanditseho is_reel = 1)
$reels_query = "SELECT p.*, c.username, c.picture 
                FROM posts p 
                JOIN clients c ON p.user_id = c.id 
                WHERE p.is_reel = 1 
                ORDER BY p.post_id DESC LIMIT 20";
$reels_result = $conn->query($reels_query);

// Fetch logged-in user info
$user_data = $conn->query("SELECT picture, username FROM clients WHERE id = $user_id")->fetch_assoc();
$my_profile_img = !empty($user_data['picture']) ? $user_data['picture'] : 'https://ui-avatars.com/api/?name=' . $user_data['username'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Bill Tolk | Reels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #000;
            overflow: hidden;
            font-family: -apple-system, sans-serif;
        }

        .reels-container {
            height: 100vh;
            scroll-snap-type: y mandatory;
            overflow-y: scroll;
            scrollbar-width: none;
            scroll-behavior: smooth;
        }

        .reels-container::-webkit-scrollbar {
            display: none;
        }

        .reel-unit {
            height: 100vh;
            width: 100vw;
            scroll-snap-align: start;
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* --- IYI NIYO NAVIGATION Y'IMYAMBI --- */
        .scroll-controls {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 30px;
            z-index: 20;
        }

        .scroll-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(5px);
            transition: 0.3s;
        }

        .scroll-btn:hover {
            background: rgba(0, 219, 222, 0.5);
        }

        .reel-sidebar {
            position: absolute;
            right: 15px;
            bottom: 120px;
            display: flex;
            flex-direction: column;
            gap: 22px;
            align-items: center;
            z-index: 10;
        }

        .action-btn {
            text-align: center;
            color: #fff;
            cursor: pointer;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        }

        .action-btn i {
            font-size: 30px;
            display: block;
            margin-bottom: 4px;
            transition: 0.2s;
        }

        .action-btn i.active {
            color: #ff4d4d;
        }

        .action-btn span {
            font-size: 13px;
            font-weight: 600;
        }

        .reel-footer {
            position: absolute;
            left: 15px;
            bottom: 95px;
            color: #fff;
            z-index: 10;
            max-width: 80%;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .user-section img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #fff;
            object-fit: cover;
        }

        .caption-text {
            font-size: 14px;
            line-height: 1.4;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 70px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }

        .nav-link {
            color: #fff;
            font-size: 24px;
            text-decoration: none;
        }

        .nav-link.active {
            color: #00dbde;
        }

        .nav-link.profile img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1.5px solid #fff;
        }
    </style>
</head>

<body>

    <div class="scroll-controls">
        <div class="scroll-btn" onclick="scrollReels('up')"><i class="fa-solid fa-chevron-up"></i></div>
        <div class="scroll-btn" onclick="scrollReels('down')"><i class="fa-solid fa-chevron-down"></i></div>
    </div>

    <div class="reels-container" id="reelsContainer">
        <?php
        if ($reels_result && $reels_result->num_rows > 0):
            while ($reel = $reels_result->fetch_assoc()):
                $p_id = $reel['post_id'];
                $poster_pic = !empty($reel['picture']) ? $reel['picture'] : 'https://ui-avatars.com/api/?name=' . $reel['username'] . '&background=00dbde&color=fff';

                $check_like = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND post_id = $p_id");
                $heart_class = ($check_like->num_rows > 0) ? "fa-solid active" : "fa-regular";
                ?>
                <div class="reel-unit">
                    <video loop muted playsinline onclick="handleVideoClick(this)">
                        <source src="<?php echo $reel['post_image']; ?>" type="video/mp4">
                    </video>

                    <div class="reel-sidebar">
                        <div class="action-btn">
                            <i class="<?php echo $heart_class; ?> fa-heart like-btn" data-id="<?php echo $p_id; ?>"
                                id="like-<?php echo $p_id; ?>"></i>
                            <span id="count-<?php echo $p_id; ?>"><?php echo number_format($reel['likes_count']); ?></span>
                        </div>
                        <div class="action-btn" onclick="alert('Comments coming soon!')">
                            <i class="fa-regular fa-comment"></i>
                            <span>View</span>
                        </div>
                        <div class="action-btn" onclick="alert('Link Copied!')">
                            <i class="fa-regular fa-paper-plane"></i>
                        </div>
                    </div>

                    <div class="reel-footer">
                        <div class="user-section">
                            <img src="<?php echo $poster_pic; ?>">
                            <b><?php echo htmlspecialchars($reel['username']); ?></b>
                        </div>
                        <div class="caption-text"><?php echo htmlspecialchars($reel['caption']); ?></div>
                    </div>
                </div>
                <?php
            endwhile;
        else:
            echo "<div style='color:white; text-align:center; margin-top:45vh; width:100%;'>No Reels yet.</div>";
        endif;
        ?>
    </div>

    <nav class="bottom-nav">
        <a href="home.php" class="nav-link"><i class="fa-solid fa-house"></i></a>
        <a href="search.php" class="nav-link"><i class="fa-solid fa-magnifying-glass"></i></a>
        <a href="chatroom.php" class="nav-link"><i class="fa-brands fa-facebook-messenger"></i></a>
        <a href="reels.php" class="nav-link active"><i class="fa-solid fa-clapperboard"></i></a>
        <a href="profile.php" class="nav-link profile"><img src="<?php echo $my_profile_img; ?>"></a>
    </nav>

    <script>
        const container = document.getElementById('reelsContainer');

        // Logic yo kumanuka no kuzamuka ukanda ku myambi
        function scrollReels(direction) {
            const reelHeight = window.innerHeight;
            if (direction === 'down') {
                container.scrollBy(0, reelHeight);
            } else {
                container.scrollBy(0, -reelHeight);
            }
        }

        // LIKE LOGIC
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
                            document.getElementById('count-' + id).innerText = data.new_count;
                        }
                    });
            });
        });

        // Play/Pause & Unmute
        function handleVideoClick(video) {
            if (video.paused) {
                video.play();
                video.muted = false;
            } else {
                video.pause();
            }
        }

        // Auto-play video mugihe ugeze kuri yo
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const video = entry.target;
                if (entry.isIntersecting) {
                    video.play().catch(() => { });
                    // Video igezeho ihite iba unmuted niba ushaka ko zumvikana
                    // video.muted = false; 
                } else {
                    video.pause();
                    video.currentTime = 0;
                }
            });
        }, { threshold: 0.7 });

        document.querySelectorAll('video').forEach(v => observer.observe(v));
    </script>
</body>

</html>