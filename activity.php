<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "chatting");
$user_id = $_SESSION['user_id'];

// 1. Reba Posts amaze gushyiraho
$my_posts = $conn->query("SELECT COUNT(*) as total FROM posts WHERE user_id = $user_id");
$posts_count = $my_posts->fetch_assoc()['total'];

// 2. Reba Likes amaze gutanga
$my_likes = $conn->query("SELECT COUNT(*) as total FROM likes WHERE user_id = $user_id");
$likes_count = $my_likes->fetch_assoc()['total'];

// 3. Reba Comments amaze kwandika
$my_comments = $conn->query("SELECT COUNT(*) as total FROM comments WHERE user_id = $user_id");
$comments_count = $my_comments->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Activity | Bill Tolk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
        }

        header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        header a {
            color: #fff;
            text-decoration: none;
            font-size: 20px;
        }

        .activity-card {
            background: #111;
            border: 1px solid #222;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info h3 {
            margin: 0;
            font-size: 16px;
            color: #00dbde;
        }

        .info p {
            margin: 5px 0 0;
            color: #888;
            font-size: 14px;
        }

        .count {
            font-size: 24px;
            font-weight: bold;
            color: #fc00ff;
        }
    </style>
</head>

<body>

    <header>
        <a href="home.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 style="margin:0;">Your Activity</h2>
    </header>

    <div class="activity-card">
        <div class="info">
            <h3>Posts</h3>
            <p>Content you've shared on your feed.</p>
        </div>
        <div class="count"><?php echo $posts_count; ?></div>
    </div>

    <div class="activity-card">
        <div class="info">
            <h3>Interactions (Likes)</h3>
            <p>Posts you have liked so far.</p>
        </div>
        <div class="count"><?php echo $likes_count; ?></div>
    </div>

    <div class="activity-card">
        <div class="info">
            <h3>Comments</h3>
            <p>Thoughts you've shared on others' posts.</p>
        </div>
        <div class="count"><?php echo $comments_count; ?></div>
    </div>

</body>

</html>