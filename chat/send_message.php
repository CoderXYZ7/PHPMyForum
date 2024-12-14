<?php
session_start();
require_once '../includes/functions.php';
requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
$roomId = $input['room_id'] ?? '';
$content = sanitizeInput($input['content'] ?? '');

if (empty($roomId) || empty($content)) {
    http_response_code(400);
    exit;
}

$user = getCurrentUser();
$messages = loadData("messages_{$roomId}.json");

$messages[] = [
    'user_id' => $_SESSION['user_id'],
    'username' => $user['username'],
    'content' => $content,
    'timestamp' => time()
];

if (saveData("messages_{$roomId}.json", $messages)) {
    http_response_code(200);
} else {
    http_response_code(500);
}
