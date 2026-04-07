<?php
session_start();

// SECURITY: Reba niba umuntu yinjiye
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
    <title>About Bill Tolk | Net Kingdom</title>
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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            max-width: 500px;
            margin: 0 auto;
            padding: 40px 20px;
            flex-grow: 1;
        }

        .logo-box {
            text-align: center;
            margin-bottom: 50px;
            margin-top: 20px;
        }

        .app-name {
            font-size: 3.2rem;
            font-weight: 900;
            letter-spacing: -1.5px;
            background: linear-gradient(45deg, #00dbde, #fc00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            text-transform: uppercase;
        }

        .version-badge {
            display: inline-block;
            background: #111;
            color: #00dbde;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            margin-top: 15px;
            border: 1px solid #1a1a1a;
            font-weight: bold;
        }

        .info-section {
            margin-top: 30px;
        }

        .info-section h3 {
            color: #00dbde;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card {
            background: #050505;
            border: 1px solid #1a1a1a;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .info-card p {
            line-height: 1.8;
            color: #ccc;
            font-size: 15px;
            margin: 0 0 15px 0;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 20px 0 0 0;
        }

        .feature-list li {
            font-size: 14px;
            color: #999;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature-list li i {
            color: #00dbde;
            font-size: 14px;
        }

        .dev-section {
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .footer {
            padding: 40px 20px;
            color: #444;
            font-size: 11px;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .heart-icon {
            color: #ff4d4d;
            animation: beat 1s infinite alternate;
        }

        @keyframes beat {
            to {
                transform: scale(1.2);
            }
        }
    </style>
</head>

<body>

    <header>
        <a href="settings.php"><i class="fa-solid fa-arrow-left"></i></a>
        <h2>About Bill Tolk</h2>
    </header>

    <main class="container">

        <div class="logo-box">
            <h1 class="app-name">BILL TOLK</h1>
            <span class="version-badge">VERSION 1.0.4 (BETA)</span>
        </div>

        <div class="info-section">
            <h3><i class="fa-solid fa-circle-info"></i> What is Bill Tolk?</h3>
            <div class="info-card">
                <p>
                    <strong>Bill Tolk</strong> ni urubuga nkoranyambaga rugezweho rwashizweho na <strong>Net
                        Kingdom</strong> kugira ngo rworoshye isabane ry'abantu b'ingeri zose hifashishijwe
                    ikoranabuhanga ryihuta.
                </p>
                <p>
                    Uru rubuga rwubakiye ku nkingi eshatu z'ingenzi: <strong>Connection</strong> (Guhuza inshuti),
                    <strong>Expression</strong> (Kwisobanura binyuze mu mafoto na video), na <strong>Security</strong>
                    (Kurinda amakuru y'abakoresha).
                </p>

                <ul class="feature-list">
                    <li><i class="fa-solid fa-bolt"></i> <strong>Real-time Chatting:</strong> Kohereza no kwakira
                        ubutumwa nta gutinda.</li>
                    <li><i class="fa-solid fa-clapperboard"></i> <strong>Posts & Reels:</strong> Gusangiza abandi
                        amafoto n'amavideo magufi.</li>
                    <li><i class="fa-solid fa-lock"></i> <strong>Safe Environment:</strong> Umutekano uhamye kuri buri
                        mukoresha.</li>
                </ul>
            </div>
        </div>

        <div class="dev-section">
            <h4 style="margin:0 0 10px 0; font-size:12px; color: #555; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fa-solid fa-shield-halved" style="color:#00dbde;"></i> Powered by
            </h4>
            <div style="font-weight: bold; font-size: 20px; color: #fff;">Net Kingdom</div>
            <div style="font-size: 13px; color: #444; margin-top: 4px;">Innovation & Excellence in Web Development</div>
        </div>

        <footer class="footer">
            &copy; 2026 BILL TOLK. ALL RIGHTS RESERVED.<br>
            MADE WITH <i class="fa-solid fa-heart heart-icon"></i> FOR THE COMMUNITY.
        </footer>

    </main>

</body>

</html>