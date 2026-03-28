<?php
// If a POST request is made, handle AI request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['text']) || empty($data['text'])) {
        echo json_encode(["reply" => "No input received"]);
        exit;
    }

    $userText = $data['text'];
    $apiKey = "YOUR_API_KEY"; // <-- Replace with your OpenAI API key

    $url = "https://api.openai.com/v1/chat/completions";

    $postData = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are a study assistant. Summarize the text and create 3 questions."],
            ["role" => "user", "content" => $userText]
        ],
        "temperature" => 0.7
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["reply" => "cURL Error: " . curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);
    $aiReply = $result['choices'][0]['message']['content'] ?? "Error: Unable to get AI response";

    echo json_encode(["reply" => $aiReply]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus</title>
<style>
body { margin:0; font-family: Arial,sans-serif; background: linear-gradient(135deg,#0f2027,#203a43,#2c5364); color:white; display:flex; flex-direction:column; height:100vh; }
.logo { text-align:center; padding:20px; }
.logo img { width:120px; }
.logo h1 { margin:10px 0; }
.chat-container { flex:1; padding:20px; overflow-y:auto; }
.message { margin-bottom:15px; }
.user { text-align:right; }
.ai { text-align:left; }
.input-area { display:flex; padding:15px; background:#111; }
textarea { flex:1; padding:15px; border-radius:10px; border:none; resize:none; }
button { margin-left:10px; padding:15px; border:none; border-radius:10px; background:#00c6ff; color:white; cursor:pointer; }
</style>
</head>
<body>

<div class="logo">
    <img src="logo.png">
    <h1>NEXUS</h1>
</div>

<div class="chat-container" id="chat"></div>

<div class="input-area">
    <textarea id="question" placeholder="Type your question..."></textarea>
    <button onclick="sendQuestion()">Send</button>
</div>

<script>
async function sendQuestion() {
    let input = document.getElementById("question");
    let chat = document.getElementById("chat");
    let question = input.value;
    if (!question) return;

    chat.innerHTML += `<div class="message user"><b>You:</b> ${question}</div>`;
    input.value = "";

    try {
        let response = await fetch("", { // Same PHP file handles request
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ text: question })
        });

        let data = await response.json();
        chat.innerHTML += `<div class="message ai"><b>AI:</b> ${data.reply}</div>`;
        chat.scrollTop = chat.scrollHeight;

    } catch (error) {
        chat.innerHTML += `<div class="message ai"><b>AI:</b> Error connecting to server</div>`;
        console.error(error);
    }
}
</script>

</body>
</html>