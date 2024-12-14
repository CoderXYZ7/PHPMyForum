<?php
session_start();
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$selectedUserId = isset($_GET['user']) ? $_GET['user'] : null;

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'send') {
        $toUserId = $_POST['to_user'];
        $message = sanitizeInput($_POST['message']);
        
        if (!empty($message) && !empty($toUserId)) {
            sendMessage($userId, $toUserId, $message);
            header('Location: messages.php?user=' . $toUserId);
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $messageId = $_POST['message_id'];
        deleteMessage($messageId, $userId);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// Mark messages as read when viewing a conversation
if ($selectedUserId) {
    markMessagesAsRead($userId, $selectedUserId);
}

$conversations = getConversations($userId);
$messages = $selectedUserId ? getMessages($userId, $selectedUserId) : [];
$users = loadData('users.json');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Base styles */
        :root {
            --primary-color: #1976d2;
            --primary-dark: #1565c0;
            --background-color: #f4f4f4;
            --surface-color: #fff;
            --text-color: #333;
            --text-secondary: #666;
            --border-color: #eee;
            --shadow: 0 2px 4px rgba(0,0,0,0.1);
            --spacing: 15px;
            --border-radius: 8px;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        /* Navigation styles */
        .nav-container {
            padding: 0 var(--spacing);
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Messages Container */
        .messages-container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
            height: calc(100vh - 100px);
            padding: 0 var(--spacing);
        }

        /* Conversations List */
        .conversations-list {
            flex: 0 0 300px;
            background: var(--surface-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow-y: auto;
            height: 100%;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--surface-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            height: 100%;
            min-width: 0; /* Prevents flex items from overflowing */
        }

        /* Conversation Items */
        .conversation-item {
            padding: var(--spacing);
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .conversation-item:hover {
            background-color: #f8f9fa;
        }

        .conversation-item.active {
            background-color: #e3f2fd;
        }

        .conversation-item .username {
            font-weight: bold;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .conversation-item .last-message {
            color: var(--text-secondary);
            font-size: 0.9em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Badge */
        .unread-badge {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8em;
            min-width: 20px;
            text-align: center;
        }

        /* Chat Components */
        .chat-header {
            padding: var(--spacing);
            border-bottom: 1px solid var(--border-color);
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-button {
            display: none;
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            padding: 0 10px;
            color: var(--text-color);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: var(--spacing);
            display: flex;
            flex-direction: column;
        }

        /* Message Items */
        .message-item {
            margin-bottom: var(--spacing);
            max-width: 80%;
            position: relative;
            word-wrap: break-word;
        }

        .message-item.sent {
            margin-left: auto;
            background-color: #e3f2fd;
            border-radius: 15px 15px 0 15px;
            padding: 10px 15px;
        }

        .message-item.received {
            background-color: #f5f5f5;
            border-radius: 15px 15px 15px 0;
            padding: 10px 15px;
        }

        .message-time {
            font-size: 0.8em;
            color: var(--text-secondary);
            margin-top: 5px;
        }

        /* Message Actions */
        .message-actions {
            display: none;
            position: absolute;
            right: 0;
            top: -20px;
        }

        .message-item:hover .message-actions {
            display: block;
        }

        .delete-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 0.8em;
            padding: 5px;
        }

        /* Chat Input */
        .chat-input {
            padding: var(--spacing);
            border-top: 1px solid var(--border-color);
        }

        .chat-input form {
            display: flex;
            gap: 10px;
        }

        .chat-input input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1em;
        }

        .chat-input button {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            white-space: nowrap;
        }

        .chat-input button:hover {
            background-color: var(--primary-dark);
        }

        /* Empty State */
        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            text-align: center;
            padding: var(--spacing);
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .messages-container {
                margin: 0;
                height: calc(100vh - 60px);
                padding: 0;
                gap: 0;
            }

            .conversations-list {
                flex: 1;
                display: flex;
                flex-direction: column;
                border-radius: 0;
            }

            .chat-area {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1000;
                border-radius: 0;
                display: none;
            }

            .chat-area.active {
                display: flex;
            }

            .back-button {
                display: block;
            }

            .message-item {
                max-width: 85%;
            }

            .chat-input {
                padding: 10px;
            }

            .chat-input input[type="text"] {
                font-size: 16px; /* Prevents zoom on iOS */
            }

            /* Hide the desktop navigation items on mobile */
            .nav-links {
                display: none;
            }

            /* Show a mobile menu button */
            .mobile-menu-button {
                display: block;
            }

            /* Mobile navigation menu */
            .mobile-nav {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: var(--surface-color);
                z-index: 2000;
                display: none;
                padding: var(--spacing);
            }

            .mobile-nav.active {
                display: block;
            }

            .mobile-nav .nav-links {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .mobile-nav a {
                padding: 10px;
                display: block;
                color: var(--text-color);
                text-decoration: none;
                border-bottom: 1px solid var(--border-color);
            }
        }

        /* Tablet Styles */
        @media (min-width: 769px) and (max-width: 1024px) {
            .messages-container {
                margin: 10px;
                height: calc(100vh - 80px);
            }

            .conversations-list {
                flex: 0 0 250px;
            }

            .message-item {
                max-width: 75%;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">Simple Forum</a>
            <button class="mobile-menu-button" onclick="toggleMobileMenu()">☰</button>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">Profile</a>
                    <a href="friends.php">Friends</a>
                    <a href="messages.php" class="active">Messages</a>
                    <a href="auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php">Login</a>
                    <a href="auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Menu -->
    <div class="mobile-nav">
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Profile</a>
                <a href="friends.php">Friends</a>
                <a href="messages.php" class="active">Messages</a>
                <a href="auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="messages-container">
        <!-- Conversations List -->
        <div class="conversations-list">
            <?php foreach ($conversations as $otherUserId => $conversation): ?>
                <div class="conversation-item <?php echo $selectedUserId === $otherUserId ? 'active' : ''; ?>"
                     onclick="openConversation('<?php echo $otherUserId; ?>')">
                    <div class="username">
                        <?php echo htmlspecialchars($conversation['user']['username']); ?>
                        <?php if ($conversation['unread_count'] > 0): ?>
                            <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="last-message">
                        <?php echo htmlspecialchars($conversation['last_message']['message']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Chat Area -->
        <div class="chat-area <?php echo $selectedUserId ? 'active' : ''; ?>" id="chatArea">
            <?php if ($selectedUserId): ?>
                <div class="chat-header">
                    <button class="back-button" onclick="closeConversation()">←</button>
                    <?php echo htmlspecialchars($users[$selectedUserId]['username']); ?>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($messages as $message): ?>
                        <div class="message-item <?php echo $message['from'] === $userId ? 'sent' : 'received'; ?>">
                            <?php if ($message['from'] === $userId): ?>
                                <div class="message-actions">
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Delete this message?')">×</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($message['message']); ?>
                            <div class="message-time">
                                <?php echo date('M j, g:i a', strtotime($message['timestamp'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chat-input">
                    <form method="post">
                        <input type="hidden" name="action" value="send">
                        <input type="hidden" name="to_user" value="<?php echo $selectedUserId; ?>">
                        <input type="text" name="message" placeholder="Type your message..." required>
                        <button type="submit">Send</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-chat-selected">
                    <h3>Select a conversation</h3>
                    <p>Choose a conversation from the list to start chatting</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom of messages
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }
        
        // Mobile navigation functions
        function toggleMobileMenu() {
            document.querySelector('.mobile-nav').classList.toggle('active');
        }

        // Mobile chat functions
        function openConversation(userId) {
            if (window.innerWidth <= 768) {
                document.getElementById('chatArea').classList.add('active');
                window.location.href = 'messages.php?user=' + userId;
            } else {
                window.location.href = 'messages.php?user=' + userId;
            }
        }

        function closeConversation() {
            if (window.innerWidth <= 768) {
                document.getElementById('chatArea').classList.remove('active');
                window.location.href = 'messages.php';
            }
        }
        
        // Scroll on page load
        scrollToBottom();
        
        // Optional: Add periodic refresh for new messages
        <?php if ($selectedUserId): ?>
        setInterval(function() {
            window.location.reload();
        }, 30000); // Refresh every 30 seconds
        <?php endif; ?>

        // Handle back button on mobile
        window.addEventListener('popstate', function(event) {
            if (window.innerWidth <= 768) {
                closeConversation();
            }
        });

        // Prevent zoom on input focus for iOS
        document.addEventListener('gesturestart', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>
