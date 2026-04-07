<?php
session_start();
// Hano koresha connection yawe (localhost, root, "", chatting)
$conn = new mysqli("localhost", "root", "", "chatting");
$user_id = $_SESSION['user_id'];

// Gufata amakuru asanzwe
$res = $conn->query("SELECT * FROM clients WHERE id = $user_id");
$data = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_user = $_POST['username'];
    $new_email = $_POST['email'];
    $new_dob = $_POST['dob'];

    $sql = "UPDATE clients SET username='$new_user', email='$new_email', dob='$new_dob' WHERE id=$user_id";
    if ($conn->query($sql)) {
        echo "<script>alert('Byahindutse!'); window.location='settings.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: sans-serif;
            padding: 20px;
        }

        .card {
            max-width: 400px;
            margin: auto;
            background: #111;
            padding: 20px;
            border-radius: 10px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background: #222;
            border: 1px solid #333;
            color: #fff;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #00dbde;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="card">
        <h3>Edit Profile</h3>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?php echo $data['username']; ?>">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo $data['email']; ?>">
            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?php echo $data['dob']; ?>">
            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>

</html>