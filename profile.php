<?php
session_start();
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $description = sanitizeInput($_POST['description']);
    
    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = 'assets/images/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $targetFile = $targetDir . $userId . '.' . $fileExtension;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            }
        }
    }

    // Load existing user data
    $users = loadData('users.json');
    if (isset($users[$userId])) {
        // Update or add new fields while preserving existing data
        $users[$userId]['description'] = $description;
        if ($imagePath) {
            $users[$userId]['image'] = $imagePath;
        }
        saveData('users.json', $users);
    }
    
    header('Location: profile.php?updated=1');
    exit;
}

$userProfile = getUserProfile($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-image {
            max-width: 200px;
            border-radius: 50%;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
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
        <div class="profile-container">
            <?php if (isset($_GET['updated'])): ?>
                <div class="success-message">Profile updated successfully!</div>
            <?php endif; ?>
            
            <h1>Your Profile</h1>
            <p>Username: <?php echo htmlspecialchars($userProfile['username']); ?></p>
            
            <?php if (!empty($userProfile['image']) && file_exists($userProfile['image'])): ?>
                <img src="<?php echo htmlspecialchars($userProfile['image']); ?>" alt="Profile Image" class="profile-image">
            <?php endif; ?>
            
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="description">About Me:</label>
                    <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($userProfile['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Profile Image:</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif">
                    <small>Supported formats: JPG, PNG, GIF</small>
                </div>
                
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>
    </main>
</body>
</html>
