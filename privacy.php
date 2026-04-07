<?php
session_start();

// 1. SECURITY: Reba niba umuntu yinjiye
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Tolk | Privacy Center</title>
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
            background: #000;
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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        .privacy-card {
            background: #050505;
            border: 1px solid #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .privacy-card h3 {
            margin-top: 0;
            color: #00dbde;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .privacy-card p {
            color: #888;
            font-size: 14px;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            background: #111;
            color: #00ff7f;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #00ff7f33;
            margin-top: 10px;
        }

        .info-box {
            background: #111;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #ccc;
            font-size: 13px;
        }

        .info-box li {
            margin-bottom: 8px;
        }
    </style>
</head>

<body>

    <header>
        <a href="settings.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h2>Privacy Center</h2>
    </header>

    <main class="container">

        <div class="privacy-card">
            <h3><i class="fa-solid fa-lock"></i> Account Privacy</h3>
            <p>Muri Bill Tolk, umutekano wawe ni wo wa mbere. Konti yawe ikoresha uburyo bwa <b>End-to-End
                    Protection</b> ku butumwa wandikira abandi.</p>
            <div class="status-badge"><i class="fa-solid fa-check-circle"></i> Protected</div>
        </div>

        <div class="privacy-card">
            <h3><i class="fa-regular fa-eye-slash"></i> Your Data</h3>
            <p>Amakuru yawe (Email, Izina, n'amafoto) akoreshwa gusa kugira ngo urubuga rukore neza. Ntabwo tuyagurisha
                cyangwa ngo tuyasangize abandi bantu batabifitiye uburenganzira.</p>
            <div class="info-box">
                <b>Ibyo tubika:</b>
                <ul>
                    <li>Amakuru watanze wiyandikisha.</li>
                    <li>Ibyo wapostinze (Posts & Reels).</li>
                    <li>Abantu wabanze (Blocked list).</li>
                </ul>
            </div>
        </div>

        <div class="privacy-card">
            <h3><i class="fa-solid fa-lightbulb"></i> Safety Tips</h3>
            <p>Kugira ngo ukomeze ube utekanye:</p>
            <div class="info-box">
                <ul>
                    <li>Ntagushyire Password yawe hanze.</li>
                    <li>Iyo urangije gukoresha urubuga kuri mudasobwa y'ahantu rusange, koresha <b>Logout</b>.</li>
                    <li>Banza abantu bose bakubangamira muri Settings.</li>
                </ul>
            </div>
        </div>

    </main>

</body>

</html>