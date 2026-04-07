<?php
session_start();

// 1. DATABASE CONNECTION
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "chatting";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// 2. REGISTRATION LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $pass = $_POST['password'];

    // --- CHECK IF USER IS 18+ ---
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;

    if ($age < 18) {
        $message = "<div style='color: #ff4d4d; margin-bottom:10px;'>Gira imyaka 18+ kugira ngo wiyandikishe (Must be 18+).</div>";
    } else {
        // --- CHECK IF EMAIL ALREADY EXISTS ---
        $checkEmail = $conn->prepare("SELECT email FROM clients WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $message = "<div style='color: #ff4d4d; margin-bottom:10px;'>Iyi email isanzwe ikoreshwa.</div>";
        } else {
            // --- PROCEED WITH REGISTRATION ---
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($_FILES["picture"]["name"]);
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {

                // GENERATE 6-DIGIT VERIFICATION CODE
                $v_code = rand(100000, 999999);

                // Insert into Database (Added v_code and is_verified defaults to 0)
                $sql = "INSERT INTO clients (username, email, dob, password, picture, v_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $user, $email, $dob, $pass, $target_file, $v_code);

                if ($stmt->execute()) {
                    // KOHEREZA EMAIL (Urugero rwa PHP mail function)
                    $to = $email;
                    $subject = "Verification Code - Bill Tolk";
                    $msg = "Muraho $user,\n\nCode yawe yo kwemeza konti ni: $v_code\n\nIyi code uyikoreshe kuri Bill Tolk.";
                    $headers = "From: support@billtolk.com";

                    // Turagerageza kuyohereza, ariko tukanayika muri Session kugira ngo verify.php iyibone
                    mail($to, $subject, $msg, $headers);

                    $_SESSION['temp_email'] = $email;

                    // Redirect to verification page instead of home.php
                    header("Location: verify.php");
                    exit();
                } else {
                    $message = "<div style='color: #ff4d4d; margin-bottom:10px;'>Database Error. Please try again.</div>";
                }
            } else {
                $message = "<div style='color: #ff4d4d; margin-bottom:10px;'>Failed to upload picture.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bill Tolk | Join</title>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .create-box {
            background: #111;
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            border: 1px solid #222;
            text-align: center;
            box-shadow: 0 0 20px rgba(252, 0, 255, 0.1);
        }

        h1 {
            background: linear-gradient(to right, #00dbde, #fc00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 3px;
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            box-sizing: border-box;
            outline: none;
        }

        label {
            display: block;
            text-align: left;
            font-size: 12px;
            color: #777;
            margin-top: 10px;
        }

        .upload-trigger {
            display: block;
            width: 100%;
            padding: 15px;
            margin-top: 10px;
            background: #222;
            border: 1px dashed #444;
            border-radius: 10px;
            color: #00dbde;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .upload-trigger:hover {
            border-color: #00dbde;
            background: #1a1a1a;
        }

        #preview-container {
            display: none;
            margin: 20px 0;
        }

        #profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fc00ff;
            box-shadow: 0 0 15px rgba(252, 0, 255, 0.3);
        }

        .btn-create {
            display: none;
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            background: linear-gradient(to right, #fc00ff, #00dbde);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-create:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(0, 219, 222, 0.4);
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #555;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="create-box">
        <h1>JOIN BILL TOLK</h1>
        <?php echo $message; ?>

        <form method="POST" enctype="multipart/form-data" id="regForm">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>

            <label>Date of Birth (Must be 18+)</label>
            <input type="date" name="dob" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" required>

            <input type="password" name="password" placeholder="Create Password" required>

            <input type="file" name="picture" id="fileInput" accept="image/*" style="display: none;" required>

            <div id="uploadBtn" class="upload-trigger" onclick="document.getElementById('fileInput').click();">
                + UPLOAD PICTURE
            </div>

            <div id="preview-container">
                <img id="profile-preview" src="#" alt="Profile Preview">
                <p style="font-size: 11px; color: #555; margin-top: 5px;">Click image to change</p>
            </div>

            <button type="submit" id="submitBtn" class="btn-create">CREATE ACCOUNT</button>
        </form>

        <a href="index.php" class="back-link">Already have an account? Log In</a>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const previewContainer = document.getElementById('preview-container');
        const previewImg = document.getElementById('profile-preview');
        const submitBtn = document.getElementById('submitBtn');

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    uploadBtn.style.display = 'none';
                    previewContainer.style.display = 'block';
                    submitBtn.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        previewImg.addEventListener('click', () => fileInput.click());
    </script>

</body>

</html>