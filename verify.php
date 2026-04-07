<?php
session_start();
$conn = new mysqli("localhost", "root", "", "chatting");

$msg = "";
if (!isset($_SESSION['temp_email'])) {
    header("Location: register.php");
    exit();
}

if (isset($_POST['verify'])) {
    $code = $_POST['code'];
    $email = $_SESSION['temp_email'];

    $query = "SELECT * FROM clients WHERE email = '$email' AND v_code = '$code'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Update is_verified to 1
        $conn->query("UPDATE clients SET is_verified = 1 WHERE email = '$email'");
        unset($_SESSION['temp_email']); // Kuramo email muri session
        header("Location: index.php?success=Account Verified! Login now.");
        exit();
    } else {
        $msg = "<div style='color:red;'>Code siyo! Reba neza kuri email yawe.</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Verify Account | Bill Tolk</title>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: Arial;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .verify-box {
            background: #111;
            padding: 30px;
            border-radius: 15px;
            border: 1px solid #222;
            text-align: center;
            width: 350px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            font-size: 20px;
            letter-spacing: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #00dbde, #fc00ff);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="verify-box">
        <h2>Verify Email</h2>
        <p style="font-size: 13px; color: #777;">Tukwoherereje code kuri <?php echo $_SESSION['temp_email']; ?></p>
        <?php echo $msg; ?>
        <form method="POST">
            <input type="text" name="code" placeholder="000000" maxlength="6" required>
            <button type="submit" name="verify">VERIFY NOW</button>
        </form>
    </div>
</body>

</html>