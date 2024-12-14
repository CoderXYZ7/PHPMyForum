<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

$postId = $_GET['id'] ?? '';
$posts = loadData('posts.json');

if (!isset($posts[$postId]) || $posts[$postId]['user_id'] !== $_SESSION['user_id']) {
    header('Location: ../index.php?page=posts');
    exit;
}

$post = $posts[$postId];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $error = "All fields are required";
    } else {
        $posts[$postId]['title'] = $title;
        $posts[$postId]['content'] = $content;
        $posts[$postId]['edited_at'] = time();
        
        if (saveData('posts.json', $posts)) {
            header('Location: ../index.php?page=posts');
            exit;
        } else {
            $error = "Failed to save changes";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Simple Forum</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main>
        <h2>Edit Post</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            
            <button type="submit" class="btn">Save Changes</button>
            <a href="../index.php?page=posts" class="btn">Cancel</a>
        </form>
    </main>
</body>
</html>
