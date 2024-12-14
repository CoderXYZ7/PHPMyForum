<?php

function saveData($filename, $data) {
    $filepath = __DIR__ . '/../data/' . $filename;
    return file_put_contents($filepath, json_encode($data));
}

function loadData($filename) {
    $filepath = __DIR__ . '/../data/' . $filename;
    if (file_exists($filepath)) {
        return json_decode(file_get_contents($filepath), true);
    }
    return [];
}

function generateId() {
    return uniqid() . bin2hex(random_bytes(8));
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /auth/login.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $users = loadData('users.json');
    return isset($users[$_SESSION['user_id']]) ? $users[$_SESSION['user_id']] : null;
}

function updateProfile($userId, $description, $imagePath) {
    $users = loadData('users.json');
    if (isset($users[$userId])) {
        $users[$userId]['description'] = $description;
        $users[$userId]['image'] = $imagePath;
        saveData('users.json', $users);
    }
}

function sendMessage($fromUserId, $toUserId, $message) {
    $messages = loadData('messages.json');
    $messageId = generateId();
    $messages[] = [
        'id' => $messageId,
        'from' => $fromUserId,
        'to' => $toUserId,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'read' => false,
        'deleted_by' => []
    ];
    saveData('messages.json', $messages);
    return $messageId;
}

function getMessages($userId, $otherUserId = null) {
    $messages = loadData('messages.json');
    return array_filter($messages, function($msg) use ($userId, $otherUserId) {
        // Skip messages marked as deleted by this user
        if (in_array($userId, $msg['deleted_by'] ?? [])) {
            return false;
        }
        
        if ($otherUserId) {
            // Return only messages between these two users
            return ($msg['to'] === $userId && $msg['from'] === $otherUserId) ||
                   ($msg['from'] === $userId && $msg['to'] === $otherUserId);
        }
        
        // Return all messages for this user
        return $msg['to'] === $userId || $msg['from'] === $userId;
    });
}

function getConversations($userId) {
    $messages = loadData('messages.json');
    $conversations = [];
    $users = loadData('users.json');

    foreach ($messages as $msg) {
        if (in_array($userId, $msg['deleted_by'] ?? [])) {
            continue;
        }
        
        if ($msg['to'] === $userId || $msg['from'] === $userId) {
            $otherUserId = $msg['to'] === $userId ? $msg['from'] : $msg['to'];
            if (!isset($conversations[$otherUserId])) {
                $conversations[$otherUserId] = [
                    'user' => $users[$otherUserId] ?? ['username' => 'Deleted User'],
                    'last_message' => $msg,
                    'unread_count' => 0
                ];
            }
            // Update last message if this one is newer
            if ($msg['timestamp'] > $conversations[$otherUserId]['last_message']['timestamp']) {
                $conversations[$otherUserId]['last_message'] = $msg;
            }
            // Count unread messages
            if ($msg['to'] === $userId && !$msg['read']) {
                $conversations[$otherUserId]['unread_count']++;
            }
        }
    }
    
    // Sort conversations by last message timestamp
    uasort($conversations, function($a, $b) {
        return strtotime($b['last_message']['timestamp']) - strtotime($a['last_message']['timestamp']);
    });
    
    return $conversations;
}

function markMessagesAsRead($userId, $fromUserId) {
    $messages = loadData('messages.json');
    $updated = false;
    
    foreach ($messages as &$msg) {
        if ($msg['to'] === $userId && $msg['from'] === $fromUserId && !$msg['read']) {
            $msg['read'] = true;
            $updated = true;
        }
    }
    
    if ($updated) {
        saveData('messages.json', $messages);
    }
}

function deleteMessage($messageId, $userId) {
    $messages = loadData('messages.json');
    foreach ($messages as &$msg) {
        if ($msg['id'] === $messageId) {
            if (!isset($msg['deleted_by'])) {
                $msg['deleted_by'] = [];
            }
            if (!in_array($userId, $msg['deleted_by'])) {
                $msg['deleted_by'][] = $userId;
            }
            break;
        }
    }
    saveData('messages.json', $messages);
}

function addFriend($userId, $friendId) {
    $friends = loadData('friends.json');
    if (!isset($friends[$userId])) {
        $friends[$userId] = [];
    }
    $friends[$userId][] = $friendId;
    saveData('friends.json', $friends);
}

function removeFriend($userId, $friendId) {
    $friends = loadData('friends.json');
    if (isset($friends[$userId])) {
        $friends[$userId] = array_filter($friends[$userId], function($id) use ($friendId) {
            return $id !== $friendId;
        });
        saveData('friends.json', $friends);
    }
}

function getFriends($userId) {
    $friends = loadData('friends.json');
    return isset($friends[$userId]) ? $friends[$userId] : [];
}

function getUserProfile($userId) {
    $users = loadData('users.json');
    return isset($users[$userId]) ? $users[$userId] : null;
}

function searchUsers($query) {
    $users = loadData('users.json');
    return array_filter($users, function($user) use ($query) {
        return strpos(strtolower($user['name']), strtolower($query)) !== false;
    });
}
