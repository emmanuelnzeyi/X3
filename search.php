<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "chatting");

// Fetch logged-in user profile for the back button or header if needed
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search | Bill Tolk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
        }

        /* Header and Search Container */
        header {
            height: 65px;
            display: flex;
            align-items: center;
            padding: 0 15px;
            border-bottom: 1px solid #1a1a1a;
            position: sticky;
            top: 0;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(15px);
            z-index: 100;
        }

        .back-btn {
            color: #fff;
            font-size: 20px;
            margin-right: 15px;
            text-decoration: none;
            transition: 0.3s;
        }

        .search-container {
            flex: 1;
            background: #1a1a1a;
            border-radius: 25px;
            /* Rounded more like modern apps */
            display: flex;
            align-items: center;
            padding: 2px 15px;
            border: 1px solid #333;
        }

        .search-container input {
            background: transparent;
            border: none;
            color: #fff;
            width: 100%;
            padding: 10px;
            outline: none;
            font-size: 16px;
        }

        .search-container i {
            color: #888;
        }

        /* Results Area */
        .results-list {
            padding: 10px 0;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Iyi niyo style ituma umuntu uje muri search asa neza */
        .user-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            text-decoration: none;
            color: #fff;
            border-bottom: 1px solid #111;
            transition: background 0.2s;
        }

        .user-item:hover,
        .user-item:active {
            background: #121212;
        }

        .user-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 1.5px solid #00dbde;
            /* Kingdom theme color */
        }

        .user-info {
            flex: 1;
        }

        .user-info h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
        }

        .user-info p {
            margin: 2px 0 0;
            font-size: 13px;
            color: #777;
        }

        .placeholder-text {
            text-align: center;
            color: #555;
            margin-top: 100px;
            font-size: 14px;
            padding: 0 20px;
        }
    </style>
</head>

<body>

    <header>
        <a href="home.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchField" placeholder="Search for friends..." autocomplete="off" autofocus>
        </div>
    </header>

    <div class="results-list" id="displayResults">
        <div class="placeholder-text">
            <i class="fa-solid fa-users-viewfinder fa-4x"
                style="margin-bottom: 20px; display: block; color: #1a1a1a;"></i>
            Find anyone in the <b>Net Kingdom</b>
        </div>
    </div>

    <script>
        const searchField = document.getElementById('searchField');
        const displayResults = document.getElementById('displayResults');

        searchField.onkeyup = () => {
            let searchTerm = searchField.value.trim();

            if (searchTerm != "") {
                let xhr = new XMLHttpRequest();
                // Iyi file niyo igomba gukora 'SELECT * FROM clients...'
                xhr.open("POST", "search_action.php", true);
                xhr.onload = () => {
                    if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                        displayResults.innerHTML = xhr.response;
                    }
                }
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                // Kohereza ijambo umuntu ari kwandika
                xhr.send("searchTerm=" + encodeURIComponent(searchTerm));
            } else {
                // Niba nta kintu ari kwandika, subiraho placeholder
                displayResults.innerHTML = `
                    <div class="placeholder-text">
                        <i class="fa-solid fa-users-viewfinder fa-4x" style="margin-bottom: 20px; display: block; color: #1a1a1a;"></i>
                        Find anyone in the <b>Net Kingdom</b>
                    </div>`;
            }
        }
    </script>

</body>

</html>