<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['post_id'] ?? '';
    $posts = loadData('posts.json');
    
    if (isset($posts[$postId]) && $posts[$postId]['user_id'] === $_SESSION['user_id']) {
        unset($posts[$postId]);
        saveData('posts.json', $posts);
    }
}

header('Location: ../index.php?page=posts');
