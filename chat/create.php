<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomName = sanitizeInput($_POST['room_name']);
    $roomDescription = sanitizeInput($_POST['room_description']);
    
    if (empty($roomName)) {
        header('Location: ../index.php?page=chat&error=name_required');
        exit;
    }
    
    $chatrooms = loadData('chatrooms.json');
    $roomId = generateId();
    
    $chatrooms[$roomId] = [
        'name' => $roomName,
        'description' => $roomDescription,
        'created_by' => $_SESSION['user_id'],
        'created_at' => time()
    ];
    
    if (saveData('chatrooms.json', $chatrooms)) {
        // Create empty message file for the new room
        saveData("messages_{$roomId}.json", []);
        header('Location: room.php?id=' . $roomId);
    } else {
        header('Location: ../index.php?page=chat&error=creation_failed');
    }
    exit;
}

header('Location: ../index.php?page=chat');
