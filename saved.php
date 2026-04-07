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

// 3. LOGIC: SOMA POSTS ZASAVINZWE (JOIN logic)
// Iyi SQL ihuriza hamwe table ya posts na saved_posts kugira ngo itange amakuru ya video/ifoto
$sql = "SELECT posts.*, saved_posts.id AS save_record_id 
        FROM posts 
        JOIN saved_posts ON posts.post_id = saved_posts.post_id 
        WHERE saved_posts.user_id = $my_id 
        ORDER BY saved_posts.id DESC";

$saved_res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Items | Bill Tolk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, sans-serif;
            margin: 0;
        }

        /* Header Style */
        .header {
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

        .header h2 {
            font-size: 18px;
            margin-left: 20px;
        }

        /* Grid Layout */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            padding: 62px 2px 20px 2px;
            /* Padding top yihariye kubera header */
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
            z-index: 5;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        .empty-state {
            grid-column: span 3;
            text-align: center;
            padding: 100px 20px;
            color: #555;
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>

<body>

    <div class="header">
        <a href="home.php"><i class="fa-solid fa-arrow-left fa-lg"></i></a>
        <h2>Saved Items</h2>
    </div>

    <div class="grid-container">
        <?php if ($saved_res->num_rows > 0): ?>
            <?php while ($row = $saved_res->fetch_assoc()):
                $file_path = $row['post_image'];
                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $is_video = in_array($ext, ['mp4', 'webm', 'mov', 'ogg']);
                ?>
                <div class="grid-item">
                    <?php if ($is_video): ?>
                        <i class="fa-solid fa-clapperboard"></i>
                        <video src="<?php echo $file_path; ?>" muted loop onmouseover="this.play()"
                            onmouseout="this.pause()"></video>
                    <?php else: ?>
                        <img src="<?php echo $file_path; ?>">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-regular fa-bookmark"></i>
                <p>Nta kintu urabika (Save) muri Kingdom yawe.</p>
                <a href="home.php" style="color: #00dbde; font-size: 14px;">Tangiye urebe posts</a>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>