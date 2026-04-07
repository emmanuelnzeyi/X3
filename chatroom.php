<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "chatting";
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

$user_id = $_SESSION['user_id'];
$chat_with = isset($_GET['chat_with']) ? (int) $_GET['chat_with'] : 0;

if ($chat_with > 0) {
    $conn->query("UPDATE notifications SET is_read = 1 
                  WHERE receiver_id = $user_id AND sender_id = $chat_with AND is_read = 0");
}

$chatting_with_name = "Public Square";
if ($chat_with > 0) {
    $name_q = "SELECT username FROM clients WHERE id = $chat_with";
    $name_r = $conn->query($name_q);
    if ($name_row = $name_r->fetch_assoc()) {
        $chatting_with_name = htmlspecialchars($name_row['username']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Tolk | <?php echo $chatting_with_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            background-color: #000;
            color: #fff;
            font-family: sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        header {
            height: 60px;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            align-items: center;
            padding: 0 20px;
            background: #050505;
        }

        .back-btn {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
        }

        .chat-user-title {
            color: #00dbde;
            font-weight: bold;
        }

        .messenger-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .user-sidebar {
            width: 300px;
            border-right: 1px solid #1a1a1a;
            background: #050505;
            overflow-y: auto;
        }

        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #000;
            position: relative;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* --- BUBBLES --- */
        .msg-wrapper {
            position: relative;
            max-width: 80%;
            cursor: pointer;
        }

        .msg-wrapper.outgoing {
            align-self: flex-end;
        }

        .msg-wrapper.incoming {
            align-self: flex-start;
        }

        /* --- ACTION BAR (Emoticons) --- */
        .action-menu {
            display: none;
            position: absolute;
            top: -45px;
            background: #1a1a1a;
            border-radius: 25px;
            padding: 7px 12px;
            gap: 15px;
            border: 1px solid #333;
            z-index: 100;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.6);
        }

        .msg-wrapper.active .action-menu {
            display: flex;
            align-items: center;
        }

        .action-menu span {
            font-size: 18px;
            transition: 0.2s;
            color: #ccc;
            cursor: pointer;
        }

        .action-menu span:hover {
            transform: scale(1.4);
            color: #00dbde;
        }

        /* --- CUSTOM FLOATING MENU (Copy/Unsend) --- */
        #customMenu {
            display: none;
            position: fixed;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            z-index: 9999;
            width: 180px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            overflow: hidden;
        }

        .menu-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #222;
            transition: 0.2s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #eee;
        }

        .menu-item:hover {
            background: #222;
        }

        .menu-item i {
            width: 20px;
            color: #00dbde;
        }

        .menu-item.delete {
            color: #ff4d4d;
        }

        .menu-item.delete i {
            color: #ff4d4d;
        }

        /* --- HEART ANIMATION --- */
        @keyframes heartFade {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }

            50% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.5);
            }

            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(2);
            }
        }

        /* --- INPUT AREA --- */
        .reply-preview {
            display: none;
            background: #0a0a0a;
            padding: 12px 20px;
            border-top: 1px solid #1a1a1a;
            border-left: 4px solid #00dbde;
        }

        .chat-input-area {
            padding: 15px 20px;
            border-top: 1px solid #1a1a1a;
            display: flex;
            gap: 10px;
            background: #050505;
            align-items: center;
        }

        .input-wrapper {
            flex: 1;
            background: #121212;
            border-radius: 25px;
            padding: 5px 20px;
            border: 1px solid #333;
        }

        .input-wrapper input {
            width: 100%;
            background: transparent;
            border: none;
            color: #fff;
            padding: 10px 0;
            outline: none;
        }

        .send-btn {
            background: none;
            border: none;
            color: #00dbde;
            font-size: 22px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <header>
        <a href="home.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <div class="chat-user-title"><?php echo $chatting_with_name; ?></div>
    </header>

    <div class="messenger-container">
        <div class="user-sidebar" id="userList"></div>

        <div class="chat-window">
            <div class="chat-messages" id="chatBox"></div>

            <div id="customMenu">
                <div class="menu-item" onclick="executeMenuAction('copy')"><i class="fa-regular fa-copy"></i> Copy Text
                </div>
                <div class="menu-item" onclick="executeMenuAction('forward')"><i class="fa-solid fa-share"></i> Forward
                </div>
                <div id="unsendOption" class="menu-item delete" onclick="executeMenuAction('unsend')"><i
                        class="fa-solid fa-trash"></i> Unsend</div>
            </div>

            <div class="reply-preview" id="replyPreview">
                <span style="float:right; cursor:pointer; color:#ff4d4d;" onclick="cancelReply()"><i
                        class="fa-solid fa-xmark"></i></span>
                <div id="replyTargetText" style="font-size:12px; color:#888;">Replying to...</div>
            </div>

            <form class="chat-input-area" id="chatForm" autocomplete="off">
                <input type="hidden" name="receiver_id" value="<?php echo $chat_with; ?>">
                <input type="hidden" name="reply_to" id="replyToInput" value="">
                <div class="input-wrapper">
                    <input type="text" name="message" placeholder="Type a message..." id="messageInput">
                </div>
                <button type="submit" class="send-btn" id="sendBtn"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <script>
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const chatBox = document.getElementById('chatBox');
        const userList = document.getElementById('userList');
        const customMenu = document.getElementById('customMenu');

        let lastResponse = "";
        let lastTap = 0;
        let shouldScroll = true;

        let currentMsgId = null;
        let currentMsgText = "";

        // 1. Gufunga menu iyo ukanze ahandi
        window.addEventListener('click', () => { customMenu.style.display = 'none'; });

        // 2. Handle Clicks on ChatBox
        chatBox.addEventListener('click', function (e) {
            let wrapper = e.target.closest('.msg-wrapper');
            if (!wrapper) {
                customMenu.style.display = 'none';
                return;
            }

            let currentTime = new Date().getTime();
            let tapLength = currentTime - lastTap;

            if (tapLength < 300 && tapLength > 0) {
                // Double Tap -> Heart Emoji
                let span = wrapper.querySelector('.action-menu span');
                if (span) {
                    let msgId = span.getAttribute('onclick').match(/\d+/)[0];
                    react(msgId, '❤️');
                    showHeartAnimation(wrapper);
                }
                e.preventDefault();
            } else {
                // Single Tap -> Show Reaction Bar
                document.querySelectorAll('.msg-wrapper').forEach(el => el.classList.remove('active'));
                wrapper.classList.toggle('active');
            }
            lastTap = currentTime;
        });

        // 3. SHOW OPTIONS (Unsend/Copy) - Ihamagarwa na get_chat.php
        function showMoreOptions(msgId, isOutgoing, msgText, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            currentMsgId = msgId;
            currentMsgText = msgText;

            // Niba message ari iyawe, unsend iragaragara
            document.getElementById('unsendOption').style.display = isOutgoing ? "flex" : "none";

            customMenu.style.display = 'block';

            let x = event.clientX;
            let y = event.clientY;

            // Adjust position
            if (x + 180 > window.innerWidth) x = x - 180;
            if (y + 150 > window.innerHeight) y = y - 150;

            customMenu.style.left = x + "px";
            customMenu.style.top = y + "px";
        }

        function executeMenuAction(action) {
            customMenu.style.display = 'none';
            if (action === 'copy') {
                navigator.clipboard.writeText(currentMsgText).then(() => alert("Copied!"));
            } else if (action === 'unsend') {
                if (confirm("Ushaka gusiba ubu butumwa kuri bose?")) {
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", "delete_msg.php", true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.onload = () => { shouldScroll = false; };
                    xhr.send("msg_id=" + currentMsgId);
                }
            } else if (action === 'forward') {
                let id = prompt("Andika ID y'uwo uyoherereje:");
                if (id) {
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", "insert_chat.php", true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.send("receiver_id=" + id + "&message=" + encodeURIComponent("Forwarded: " + currentMsgText));
                }
            }
        }

        // --- Other Functions ---
        function showHeartAnimation(el) {
            let h = document.createElement('div');
            h.innerHTML = "❤️";
            h.style.cssText = "position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-size:50px; animation:heartFade 0.8s forwards; pointer-events:none;";
            el.appendChild(h);
            setTimeout(() => h.remove(), 800);
        }

        function setReply(id, txt) {
            document.getElementById('replyToInput').value = id;
            document.getElementById('replyTargetText').innerText = "Replying to: " + txt.substring(0, 30);
            document.getElementById('replyPreview').style.display = "block";
            messageInput.focus();
        }

        function cancelReply() {
            document.getElementById('replyToInput').value = "";
            document.getElementById('replyPreview').style.display = "none";
        }

        function react(id, emo) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "save_reaction.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("msg_id=" + id + "&emoji=" + emo);
        }

        // --- AJAX REFRESH ---
        document.getElementById('sendBtn').onclick = (e) => {
            e.preventDefault();
            if (messageInput.value.trim() === "") return;
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "insert_chat.php", true);
            xhr.onload = () => {
                if (xhr.status === 200) {
                    messageInput.value = "";
                    cancelReply();
                    shouldScroll = true;
                }
            };
            xhr.send(new FormData(chatForm));
        };

        setInterval(() => {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "get_chat.php", true);
            xhr.onload = () => {
                if (xhr.status === 200 && xhr.response !== lastResponse) {
                    chatBox.innerHTML = xhr.response;
                    lastResponse = xhr.response;
                    if (shouldScroll) chatBox.scrollTop = chatBox.scrollHeight;
                }
            };
            xhr.send(new FormData(chatForm));
        }, 500);

        setInterval(() => {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "get_users.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = () => { if (xhr.status === 200) userList.innerHTML = xhr.response; };
            xhr.send("chat_with=<?php echo $chat_with; ?>");
        }, 2000);

    </script>
</body>

</html>