<?php
session_start();

// 1. SECURITY: Reba niba umuntu yinjiye (Logged in)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "chatting");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Tolk | Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background-color: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        header {
            height: 60px;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            align-items: center;
            padding: 0 15px;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(15px);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .back-btn {
            color: #fff;
            text-decoration: none;
            font-size: 20px;
            margin-right: 20px;
            cursor: pointer;
            transition: 0.2s;
        }

        .back-btn:hover {
            color: #00dbde;
        }

        header h2 {
            font-size: 1.2rem;
            margin: 0;
            font-weight: 600;
        }

        .settings-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 10px;
        }

        .section-title {
            padding: 20px 15px 10px;
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .settings-list {
            background: #050505;
            border-radius: 12px;
            border: 1px solid #1a1a1a;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .settings-item {
            display: flex;
            align-items: center;
            padding: 16px 15px;
            text-decoration: none;
            color: #fff;
            transition: background 0.2s;
            border-bottom: 1px solid #1a1a1a;
        }

        .settings-item:last-child {
            border-bottom: none;
        }

        .settings-item:hover {
            background: #111;
        }

        .settings-item i.icon {
            width: 35px;
            font-size: 18px;
            color: #00dbde;
            /* Iri bara rya Cyan rya Bill Tolk */
        }

        .settings-item span {
            flex-grow: 1;
            font-size: 15px;
        }

        .settings-item i.arrow {
            color: #333;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <header>
        <a href="home.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2>Settings</h2>
    </header>

    <main class="settings-container">

        <div class="section-title">Account Settings</div>
        <div class="settings-list">
            <a href="edit_profile.php" class="settings-item">
                <i class="fa-solid fa-user-pen icon"></i>
                <span>Edit Profile</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <a href="change_password.php" class="settings-item">
                <i class="fa-solid fa-key icon"></i>
                <span>Change Password</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
        </div>

        <div class="section-title">Privacy & Social</div>
        <div class="settings-list">
            <a href="blocked_users.php" class="settings-item">
                <i class="fa-solid fa-user-slash icon"></i>
                <span>Blocked Accounts</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <a href="privacy.php" class="settings-item">
                <i class="fa-solid fa-shield-halved icon"></i>
                <span>Privacy Center</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
        </div>

        <div class="section-title">Support & Info</div>
        <div class="settings-list">
            <a href="about.php" class="settings-item">
                <i class="fa-solid fa-circle-info icon"></i>
                <span>About Bill Tolk</span>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>
        </div>

    </main>

</body>

</html>