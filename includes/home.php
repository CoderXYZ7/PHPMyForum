<?php
$posts = loadData('posts.json');
$chatrooms = loadData('chatrooms.json');

// Get recent posts
usort($posts, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});
$recentPosts = array_slice($posts, 0, 5);

// Get active chatrooms
$activeChatrooms = array_slice($chatrooms, 0, 5);
?>

<div class="home-section">
    <h1>Welcome to Simple Forum</h1>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="welcome-message">
            <p>Join our community to participate in discussions and chat with other members.</p>
            <div class="action-buttons">
                <a href="auth/login.php" class="btn">Login</a>
                <a href="auth/register.php" class="btn">Register</a>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="recent-activity">
        <div class="recent-posts">
            <h2>Recent Posts</h2>
            <?php if (empty($recentPosts)): ?>
                <p>No posts yet. Be the first to create one!</p>
            <?php else: ?>
                <?php foreach ($recentPosts as $postId => $post): ?>
                    <div class="post-preview">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <div class="post-meta">
                            by <?php echo htmlspecialchars($post['username']); ?>
                            on <?php echo date('M j, Y', $post['timestamp']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="active-chatrooms">
            <h2>Active Chat Rooms</h2>
            <?php if (empty($activeChatrooms)): ?>
                <p>No chat rooms available. Create one to start chatting!</p>
            <?php else: ?>
                <?php foreach ($activeChatrooms as $roomId => $room): ?>
                    <div class="chatroom-preview">
                        <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                        <p><?php echo htmlspecialchars($room['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
