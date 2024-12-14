<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

$roomId = $_GET['id'] ?? '';
$chatrooms = loadData('chatrooms.json');

if (!isset($chatrooms[$roomId])) {
    header('Location: ../index.php?page=chat');
    exit;
}

$room = $chatrooms[$roomId];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($room['name']); ?> - Chat Room</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="chat-container">
        <h2><?php echo htmlspecialchars($room['name']); ?></h2>
        <div class="chat-messages" id="messages"></div>
        
        <form id="message-form" class="message-form">
            <div class="form-group">
                <input type="text" id="message" name="message" placeholder="Type your message..." required>
            </div>
            <button type="submit" class="btn">Send</button>
        </form>
    </div>

    <script>
    const roomId = '<?php echo $roomId; ?>';
    const userId = '<?php echo $_SESSION['user_id']; ?>';
    
    function loadMessages() {
        fetch(`get_messages.php?room=${roomId}`)
            .then(response => response.json())
            .then(messages => {
                const messagesDiv = document.getElementById('messages');
                messagesDiv.innerHTML = messages.map(msg => `
                    <div class="message">
                        <strong>${msg.username}:</strong> ${msg.content}
                        <small>${new Date(msg.timestamp * 1000).toLocaleString()}</small>
                    </div>
                `).join('');
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            });
    }

    document.getElementById('message-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const messageInput = document.getElementById('message');
        const content = messageInput.value;
        
        await fetch('send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                room_id: roomId,
                content: content
            }),
        });
        
        messageInput.value = '';
        loadMessages();
    });

    // Load messages initially and every 3 seconds
    loadMessages();
    setInterval(loadMessages, 3000);
    </script>
</body>
</html>
