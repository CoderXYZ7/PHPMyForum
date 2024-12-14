<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    
    if (empty($title) || empty($content)) {
        header('Location: ../index.php?page=posts&error=fields_required');
        exit;
    }
    
    $posts = loadData('posts.json');
    $user = getCurrentUser();
    $postId = generateId();
    
    $posts[$postId] = [
        'title' => $title,
        'content' => $content,
        'user_id' => $_SESSION['user_id'],
        'username' => $user['username'],
        'timestamp' => time()
    ];
    
    if (saveData('posts.json', $posts)) {
        header('Location: ../index.php?page=posts');
    } else {
        header('Location: ../index.php?page=posts&error=creation_failed');
    }
    exit;
}

header('Location: ../index.php?page=posts');
