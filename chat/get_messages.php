<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

$roomId = $_GET['room'] ?? '';
$messages = loadData("messages_{$roomId}.json");

// Sort messages by timestamp
usort($messages, function($a, $b) {
    return $a['timestamp'] - $b['timestamp'];
});

header('Content-Type: application/json');
echo json_encode($messages);
