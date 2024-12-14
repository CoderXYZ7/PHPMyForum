<?php
session_start();
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Forum</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">Simple Forum</a>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">Profile</a>
                    <a href="messages.php">Messages</a>
                    <a href="friends.php">Friends</a>
                    <a href="index.php?page=chat">Chat Rooms</a>
                    <a href="index.php?page=posts">Posts</a>
                    <a href="auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php">Login</a>
                    <a href="auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main>
        <?php
        switch($page) {
            case 'home':
                include 'includes/home.php';
                break;
            case 'chat':
                include 'chat/index.php';
                break;
            case 'posts':
                include 'posts/index.php';
                break;
            default:
                include 'includes/home.php';
        }
        ?>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
