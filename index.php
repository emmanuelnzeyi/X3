<?php
session_start();

// 1. DATABASE SETTINGS
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "chatting";

// Connect to MySQL
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// 2. LOGIN LOGIC
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Using Prepared Statements for Security
    $sql = "SELECT * FROM clients WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Checking if password matches
        if ($password == $user['password']) {
            // SET SESSIONS
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // REDIRECT TO HOME.PHP (Your new feed)
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Invalid Password. Please try again.";
        }
    } else {
        $error_message = "Account not found. Please create an account.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Tolk | Login</title>
    <style>
        body {
            background-color: #000000;
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .login-box {
            background: #0a0a0a;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 0 40px rgba(0, 219, 222, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid #1a1a1a;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            background: linear-gradient(to right, #00dbde, #fc00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            color: #555;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-field {
            width: 100%;
            padding: 16px;
            margin-bottom: 15px;
            background: #111;
            border: 1px solid #222;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }

        .input-field:focus {
            border-color: #00dbde;
            background: #151515;
            box-shadow: 0 0 10px rgba(0, 219, 222, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #00dbde, #fc00ff);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(252, 0, 255, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error {
            background: rgba(255, 77, 77, 0.1);
            color: #ff4d4d;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid rgba(255, 77, 77, 0.2);
        }

        .footer-links {
            margin-top: 25px;
            font-size: 14px;
        }

        .btn-create {
            color: #00dbde;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-create:hover {
            color: #fc00ff;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h1>BILL TOLK</h1>
        <p class="subtitle">Enter your kingdom</p>

        <?php if ($error_message): ?>
            <div class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <input type="email" name="email" class="input-field" placeholder="Email Address" required>
            <input type="password" name="password" class="input-field" placeholder="Password" required>
            <button type="submit" class="btn-login" id="loginBtn">LOG IN</button>
        </form>

        <div class="footer-links">
            <span style="color: #444;">Don't have an account?</span>
            <a href="create.php" class="btn-create">Create one</a><br>

        </div>
    </div>

    <script>
        // Smooth UI: Change button text to "Verifying..." to prevent double-clicks
        const form = document.getElementById('loginForm');
        const btn = document.getElementById('loginBtn');

        form.onsubmit = function () {
            btn.innerHTML = "VERIFYING...";
            btn.style.opacity = "0.7";
            btn.style.cursor = "not-allowed";
        };
    </script>

</body>

</html>