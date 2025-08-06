<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$username = $_SESSION['user']['username'];

// Handle message send (via POST, normal or AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $stmt = $conn->prepare("INSERT INTO notifications (message, sender_id, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("si", $msg, $user_id);
        $stmt->execute();
        $stmt->close();

        // AJAX request response
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['status' => 'success']);
            exit;
        } else {
            header("Location: notifications.php");
            exit();
        }
    }
}

// Handle delete message
if (isset($_GET['delete'])) {
    $msg_id = (int)$_GET['delete'];
    // Only delete if sender is current user
    $conn->query("DELETE FROM notifications WHERE id = $msg_id AND sender_id = $user_id");
    $conn->query("DELETE FROM notification_views WHERE notification_id = $msg_id");
    header("Location: notifications.php");
    exit();
}

// AJAX fetch messages
if (isset($_GET['action']) && $_GET['action'] === 'fetch_messages') {
    $messages = [];
    $res = $conn->query("
        SELECT n.*, u.username 
        FROM notifications n 
        JOIN users u ON n.sender_id = u.id 
        ORDER BY n.created_at ASC
    ");

    while ($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($messages);
    exit;
}

// For initial page load, no need to fetch messages here anymore (AJAX will do it)
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Chat</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        background: #f4f4f9;
    }

    .container {
        display: flex;
        height: 100vh;
        flex-wrap: wrap;
    }

    .sidebar {
        width: 250px;
        background: #4a90e2;
        color: white;
        padding: 20px;
        overflow-y: auto;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar h3 {
        margin-top: 0;
    }

    .sidebar .user {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        transition: background 0.3s;
    }

    .sidebar .user:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .chat {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        padding: 20px;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .messages {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 10px;
    }

    .message {
        margin-bottom: 15px;
        padding: 10px;
        background: #e9ecef;
        border-radius: 10px;
        max-width: 70%;
        position: relative;
        transition: background 0.3s;
        word-wrap: break-word;
    }

    .me {
        background: #c3e6cb;
        margin-left: auto;
    }

    .message:hover {
        background: #d3d3d3;
    }

    .meta {
        font-size: 13px;
        color: #555;
        margin-top: 5px;
    }

    .views {
        font-size: 12px;
        color: #888;
        margin-top: 3px;
    }

    form {
        display: flex;
        padding-top: 10px;
    }

    input[type="text"] {
        flex: 1;
        padding: 12px;
        font-size: 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        transition: border 0.3s;
    }

    input[type="text"]:focus {
        border-color: #4a90e2;
        outline: none;
    }

    button {
        padding: 12px 20px;
        margin-left: 10px;
        border: none;
        background-color: #4a90e2;
        color: white;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s;
    }

    button:hover {
        background-color: #3c7ab0ff;
    }

    .delete {
        position: absolute;
        top: 6px;
        right: 8px;
        font-size: 16px;
        color: red;
        cursor: pointer;
        text-decoration: none;
    }

    .delete:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            height: auto;
        }

        .chat {
            height: 100vh;
        }
    }
</style>
</head>
<body>

<div class="container">
    <!-- Left Sidebar -->
    <div class="sidebar">
        <h3>💬 Chat Users</h3>
        <div class="user"><strong><?= htmlspecialchars($username) ?> (You)</strong></div>
        <?php
        $userList = $conn->query("SELECT username FROM users WHERE id != $user_id");
        while ($u = $userList->fetch_assoc()):
        ?>
            <div class="user"><?= htmlspecialchars($u['username']) ?></div>
        <?php endwhile; ?>
    </div>

    <!-- Right Chat Window -->
    <div class="chat">
        <div class="messages" id="chatbox"></div>

        <form method="POST" id="chat-form">
            <input type="text" name="message" placeholder="Type your message..." required autocomplete="off" />
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<script>
    const chatbox = document.getElementById('chatbox');
    const form = document.getElementById('chat-form');
    const input = form.querySelector('input[name="message"]');
    const currentUserId = <?= json_encode($user_id) ?>;

    function scrollChat() {
        chatbox.scrollTop = chatbox.scrollHeight;
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
        });
    }

    function renderMessages(messages) {
        chatbox.innerHTML = '';
        messages.forEach(msg => {
            const div = document.createElement('div');
            div.classList.add('message');
            if (msg.sender_id == currentUserId) div.classList.add('me');

            // Message text
            div.innerHTML = escapeHtml(msg.message);

            // Meta info
            const meta = document.createElement('div');
            meta.classList.add('meta');
            const createdAt = new Date(msg.created_at);
            meta.textContent = `${escapeHtml(msg.username)} • ${createdAt.toLocaleString()}`;
            div.appendChild(meta);

            // Delete button if own message
            if (msg.sender_id == currentUserId) {
                const del = document.createElement('a');
                del.href = `?delete=${msg.id}`;
                del.textContent = '🗑';
                del.classList.add('delete');
                del.onclick = function(e) {
                    return confirm('Delete this message?');
                };
                div.appendChild(del);
            }

            chatbox.appendChild(div);
        });
        scrollChat();
    }

    // Fetch messages from server
    function fetchMessages() {
        fetch('notifications.php?action=fetch_messages')
            .then(res => res.json())
            .then(data => {
                renderMessages(data);
            }).catch(err => {
                console.error('Error fetching messages', err);
            });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;

        fetch('notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'message=' + encodeURIComponent(msg)
        }).then(res => res.json())
          .then(response => {
            if (response.status === 'success') {
                input.value = '';
                fetchMessages();
            }
        }).catch(err => {
            console.error('Message send failed', err);
        });
    });

    // Initial fetch and polling
    fetchMessages();
    setInterval(fetchMessages, 3000);
</script>

</body>
</html>
