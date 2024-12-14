<?php
requireLogin();
$posts = loadData('posts.json');

// Sort posts by timestamp, newest first
usort($posts, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});
?>

<div class="posts-section">
    <h2>Forum Posts</h2>
    
    <div class="create-post">
        <h3>Create New Post</h3>
        <form action="posts/create.php" method="POST">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" required></textarea>
            </div>
            
            <button type="submit" class="btn">Create Post</button>
        </form>
    </div>
    
    <div class="posts-list">
        <?php foreach ($posts as $postId => $post): ?>
            <div class="post">
                <div class="post-header">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <div class="post-meta">
                        Posted by <?php echo htmlspecialchars($post['username']); ?>
                        on <?php echo date('M j, Y g:i A', $post['timestamp']); ?>
                    </div>
                </div>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                <?php if ($_SESSION['user_id'] === $post['user_id']): ?>
                    <div class="post-actions">
                        <a href="posts/edit.php?id=<?php echo $postId; ?>" class="btn">Edit</a>
                        <form action="posts/delete.php" method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                            <button type="submit" class="btn" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
