<?php
requireLogin();
$chatrooms = loadData('chatrooms.json');
?>

<div class="chat-section">
    <h2>Chat Rooms</h2>
    
    <div class="chatroom-list">
        <?php foreach ($chatrooms as $roomId => $room): ?>
            <div class="chatroom">
                <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                <p><?php echo htmlspecialchars($room['description']); ?></p>
                <a href="chat/room.php?id=<?php echo $roomId; ?>" class="btn">Join Chat</a>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="create-chatroom">
        <h3>Create New Chat Room</h3>
        <form action="chat/create.php" method="POST">
            <div class="form-group">
                <label for="room_name">Room Name:</label>
                <input type="text" id="room_name" name="room_name" required>
            </div>
            
            <div class="form-group">
                <label for="room_description">Description:</label>
                <textarea id="room_description" name="room_description" required></textarea>
            </div>
            
            <button type="submit" class="btn">Create Room</button>
        </form>
    </div>
</div>
