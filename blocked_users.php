<?php
session_start();

// 1. SECURITY: Reba niba umuntu yinjiye
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

// --- 3. LOGIC YO KUBURA UMUNTU (UNBLOCK) ---
if (isset($_GET['unblock_id'])) {
    $unblock_id = intval($_GET['unblock_id']);
    $unblock_query = "DELETE FROM blocked_accounts WHERE blocker_id = $user_id AND blocked_id = $unblock_id";

    if ($conn->query($unblock_query)) {
        // Nyuma yo kumukura mu muhezo, musubize kuri iyi page nyine
        header("Location: blocked_users.php?msg=unblocked");
        exit();
    }
}

// --- 4. GUFATA URUTONDE RW'ABO WABANZE ---
// Tunafata amakuru yabo muri table ya "clients" kugira ngo turyerekeze
$query = "SELECT clients.id, clients.username, clients.picture 
          FROM blocked_accounts 
          JOIN clients ON blocked_accounts.blocked_id = clients.id 
          WHERE blocked_accounts.blocker_id = $user_id 
          ORDER BY blocked_accounts.blocked_at DESC";
$blocked_list = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Tolk | Blocked Accounts</title>
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
        }

        header {
            height: 60px;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            align-items: center;
            padding: 0 15px;
            background: rgba(0, 0, 0, 0.9);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header a {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
            font-size: 20px;
        }

        header h2 {
            font-size: 1.1rem;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 10px;
        }

        .info-text {
            padding: 15px;
            font-size: 13px;
            color: #888;
            line-height: 1.4;
        }

        .blocked-user-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            background: #050505;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
            border: 1px solid #222;
        }

        .username {
            font-weight: 600;
            font-size: 14px;
        }

        .unblock-btn {
            background: #fff;
            color: #000;
            border: none;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }

        .unblock-btn:hover {
            background: #00dbde;
            /* Iri bara ryawe rya Cyan */
            color: #fff;
        }

        .empty-state {
            text-align: center;
            margin-top: 80px;
            color: #444;
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <header>
        <a href="settings.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h2>Blocked Accounts</h2>
    </header>

    <main class="container">
        <div class="info-text">
            Iyo ubanze umuntu (Block), ntabwo ashobora kukwandikira, kureba profile yawe, cyangwa kubona ibyo wapostinze
            kuri Bill Tolk.
        </div>

        <?php if ($blocked_list && $blocked_list->num_rows > 0): ?>
            <?php while ($user = $blocked_list->fetch_assoc()):
                // Logic y'ifoto nko muri home.php
                $p_img = !empty($user['picture']) ? $user['picture'] : 'https://ui-avatars.com/api/?name=' . $user['username'] . '&background=00dbde&color=fff';
                ?>
                <div class="blocked-user-card">
                    <div class="user-info">
                        <img src="<?php echo $p_img; ?>" alt="profile">
                        <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <a href="blocked_users.php?unblock_id=<?php echo $user['id']; ?>"
                        onclick="return confirm('Ese urashaka gukura <?php echo $user['username']; ?> mu muhezo?')"
                        class="unblock-btn">Unblock</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-user-lock"></i>
                <p>Nta muntu n'umwe wabonye (No blocked users).</p>
            </div>
        <?php endif; ?>
    </main>

</body>

</html>