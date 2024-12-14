<?php
session_start();
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$friends = getFriends($userId);
$users = loadData('users.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $friendId = $_POST['friend_id'];
        
        if ($_POST['action'] === 'add') {
            addFriend($userId, $friendId);
        } elseif ($_POST['action'] === 'remove') {
            removeFriend($userId, $friendId);
        }
    }
    header('Location: friends.php');
    exit;
}

// Get potential friends (users who are not already friends)
$potentialFriends = array_filter($users, function($id) use ($userId, $friends) {
    return $id !== $userId && !in_array($id, $friends);
}, ARRAY_FILTER_USE_KEY);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .friends-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .friends-list {
            list-style: none;
            padding: 0;
        }
        .friend-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .friend-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .friend-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .friend-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">Simple Forum</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="profile.php">Profile</a>
                <a href="messages.php">Messages</a>
                <a href="friends.php">Friends</a>
                <a href="index.php?page=chat">Chat Rooms</a>
                <a href="index.php?page=posts">Posts</a>
                <a href="auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <main>
        <div class="friends-container">
            <div class="section">
                <h1>Your Friends</h1>
                <?php if (empty($friends)): ?>
                    <p>You don't have any friends yet.</p>
                <?php else: ?>
                    <ul class="friends-list">
                        <?php foreach ($friends as $friendId): ?>
                            <?php if (isset($users[$friendId])): ?>
                                <li class="friend-item">
                                    <div class="friend-info">
                                        <?php if (!empty($users[$friendId]['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($users[$friendId]['image']); ?>" alt="" class="friend-avatar">
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($users[$friendId]['username']); ?></span>
                                    </div>
                                    <div class="friend-actions">
                                        <form action="messages.php" method="GET">
                                            <input type="hidden" name="to" value="<?php echo $friendId; ?>">
                                            <button type="submit" class="btn btn-primary">Message</button>
                                        </form>
                                        <form action="friends.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="friend_id" value="<?php echo $friendId; ?>">
                                            <button type="submit" class="btn btn-danger">Remove Friend</button>
                                        </form>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="section">
                <h2>Add New Friends</h2>
                <?php if (empty($potentialFriends)): ?>
                    <p>No new users to add as friends.</p>
                <?php else: ?>
                    <ul class="friends-list">
                        <?php foreach ($potentialFriends as $userId => $user): ?>
                            <li class="friend-item">
                                <div class="friend-info">
                                    <?php if (!empty($user['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['image']); ?>" alt="" class="friend-avatar">
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                                <form action="friends.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="friend_id" value="<?php echo $userId; ?>">
                                    <button type="submit" class="btn btn-primary">Add Friend</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
