<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Messenger - Alpha Version</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        .header h1 {
            color: #e94560;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        .header p {
            color: #8f8f8f;
            max-width: 600px;
            margin: 0 auto;
            font-size: 1.1rem;
        }
        .app-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .app-window {
            width: 100%;
            max-width: 1000px;
            height: 750px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            background: white;
        }

        /* Login Form Styles */
        .login-container { padding: 40px; color: white; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; background: #1a1a2e; }
        .login-container h2 { color: #e94560; margin-bottom: 20px; text-align: center; }
        .login-form .form-group { margin-bottom: 15px; }
        .login-form label { display: block; margin-bottom: 5px; color: #8f8f8f; }
        .login-form input { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #3a3a5a; background: #1a1a2e; color: white; }
        .login-form button { width: 100%; padding: 12px; border: none; border-radius: 8px; background: #e94560; color: white; font-weight: 600; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .login-form button:hover { background: #c93550; }
        .login-error { color: #e94560; margin-bottom: 15px; text-align: center; }

        /* Chat UI Styles */
        #chat-app { display: flex; height: 100%; color: #333; }
        .sidebar { width: 300px; border-right: 1px solid #eee; background: #f7f7f7; overflow-y: auto; display: flex; flex-direction: column; }
        .sidebar-section { padding: 20px; border-bottom: 1px solid #eee; }
        .sidebar-section h3 { color: #e94560; margin-bottom: 15px; }
        .sidebar-section ul { list-style: none; }
        .sidebar-section li { padding: 10px; cursor: pointer; border-radius: 5px; margin-bottom: 5px; }
        .sidebar-section li:hover, .sidebar-section li.active { background: #e9e9e9; }
        #my-unique-id-display { font-weight: bold; color: #333; }

        #add-friend-form { display: flex; gap: 5px; }
        #add-friend-input { flex-grow: 1; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        #add-friend-form button { padding: 8px 12px; border: none; background: #e94560; color: white; border-radius: 5px; cursor: pointer; }
        
        #search-results li, #friend-requests li { display: flex; justify-content: space-between; align-items: center; }
        .action-btn { padding: 5px 8px; border: none; border-radius: 5px; color: white; cursor: pointer; }
        .add-btn { background: #4CAF50; }
        .accept-btn { background: #4CAF50; }
        .decline-btn { background: #f44336; margin-left: 5px; }

        .chat-area { flex-grow: 1; display: flex; flex-direction: column; }
        .chat-header { background: #f7f7f7; padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .chat-header h3 { color: #e94560; font-size: 1.2rem; }
        .logout-btn { color: #e94560; text-decoration: none; font-weight: 600; }
        
        .chat-messages { flex-grow: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        .message { max-width: 80%; padding: 10px 15px; border-radius: 18px; line-height: 1.4; }
        .message.sent { background: #e94560; color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .message.received { background: #f1f1f1; color: #333; align-self: flex-start; border-bottom-left-radius: 4px; }
        .chat-welcome { text-align: center; color: #aaa; margin-top: 50px; }

        .chat-input-form { display: flex; padding: 15px; border-top: 1px solid #eee; background: #f7f7f7; }
        .chat-input-form input { flex-grow: 1; border: 1px solid #ddd; border-radius: 20px; padding: 10px 15px; font-size: 1rem; }
        .chat-input-form button { background: #e94560; color: white; border: none; border-radius: 20px; padding: 10px 20px; margin-left: 10px; cursor: pointer; font-weight: 600; }
        .image-upload-btn { padding: 10px; cursor: pointer; font-size: 1.5rem; color: #8f8f8f; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Secure Messenger</h1>
            <p>Alpha Version - <?php echo isset($_SESSION['user_id']) ? "Welcome, " . htmlspecialchars($_SESSION['username']) : "Please log in"; ?></p>
        </div>
        <div class="app-container">
            <div id="app-window" class="app-window">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div id="chat-app">
                        <div class="sidebar">
                            <div class="sidebar-section">
                                <h3>Your Unique ID</h3>
                                <p id="my-unique-id-display">Loading...</p>
                            </div>
                            <div class="sidebar-section">
                                <h3>Add a Friend</h3>
                                <form id="add-friend-form">
                                    <input type="text" id="add-friend-input" placeholder="Enter Unique ID">
                                    <button type="submit">Search</button>
                                </form>
                                <ul id="search-results"></ul>
                            </div>
                            <div class="sidebar-section">
                                <h3>Friend Requests</h3>
                                <ul id="friend-requests"></ul>
                            </div>
                            <div class="sidebar-section">
                                <h3>Friends</h3>
                                <ul id="friends-ul"></ul>
                            </div>
                        </div>
                        <div class="chat-area">
                            <div class="chat-header">
                                <h3 id="chat-with-user">Select a friend to chat</h3>
                                <div>
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                        <a href="admin.php" class="logout-btn">Admin</a> | 
                                    <?php endif; ?>
                                    <a href="logout.php" class="logout-btn">Logout</a>
                                </div>
                            </div>
                            <div class="chat-messages" id="chat-messages">
                                <div class="chat-welcome">
                                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                                    <p>Select a friend from the list to start a conversation.</p>
                                </div>
                            </div>
                            <form class="chat-input-form" id="message-form" style="display: none;" enctype="multipart/form-data">
                                <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off">
                                <input type="file" id="image-input" accept="image/*" style="display: none;">
                                <label for="image-input" class="image-upload-btn">&#128247;</label>
                                <button type="submit">Send</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="login-container">
                        <h2>Login</h2>
                        <?php
                        if (isset($_SESSION['login_error'])) {
                            echo '<p class="login-error">' . htmlspecialchars($_SESSION['login_error']) . '</p>';
                            unset($_SESSION['login_error']);
                        }
                        ?>
                        <form action="login.php" method="POST" class="login-form">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" id="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" id="password" required>
                            </div>
                            <button type="submit">Login</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        <?php if (isset($_SESSION['user_id'])): ?>
        const friendsUl = document.getElementById('friends-ul');
        const chatMessages = document.getElementById('chat-messages');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const imageInput = document.getElementById('image-input');
        const chatWithUser = document.getElementById('chat-with-user');
        const myUniqueIdDisplay = document.getElementById('my-unique-id-display');
        const addFriendForm = document.getElementById('add-friend-form');
        const addFriendInput = document.getElementById('add-friend-input');
        const searchResultsUl = document.getElementById('search-results');
        const friendRequestsUl = document.getElementById('friend-requests');
        
        let currentRecipientId = null;
        let messagePollingInterval = null;

        async function getMyUniqueId() {
            const response = await fetch('api.php?action=get_my_unique_id');
            const data = await response.json();
            if (data.unique_id) {
                myUniqueIdDisplay.textContent = data.unique_id;
            }
        }

        addFriendForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const query = addFriendInput.value.trim();
            if (!query) return;
            const response = await fetch(`api.php?action=search_users&query=${query}`);
            const users = await response.json();
            searchResultsUl.innerHTML = '';
            users.forEach(user => {
                const li = document.createElement('li');
                li.innerHTML = `<span>${user.username} (${user.unique_id})</span> <button class="action-btn add-btn" data-user-id="${user.id}">Add</button>`;
                searchResultsUl.appendChild(li);
            });
        });

        searchResultsUl.addEventListener('click', async (e) => {
            if (e.target.classList.contains('add-btn')) {
                const userId = e.target.dataset.userId;
                const response = await fetch('api.php?action=send_friend_request', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Friend request sent!');
                    e.target.parentElement.remove();
                } else {
                    alert(`Error: ${result.error}`);
                }
            }
        });

        async function getFriendRequests() {
            const response = await fetch('api.php?action=get_friend_requests');
            const requests = await response.json();
            friendRequestsUl.innerHTML = '';
            requests.forEach(req => {
                const li = document.createElement('li');
                li.innerHTML = `<span>${req.username} (${req.unique_id})</span> <div><button class="action-btn accept-btn" data-request-id="${req.id}">Accept</button><button class="action-btn decline-btn" data-request-id="${req.id}">Decline</button></div>`;
                friendRequestsUl.appendChild(li);
            });
        }

        friendRequestsUl.addEventListener('click', async (e) => {
            const target = e.target;
            const requestId = target.dataset.requestId;
            let status = '';
            if (target.classList.contains('accept-btn')) {
                status = 'accepted';
            } else if (target.classList.contains('decline-btn')) {
                status = 'declined';
            } else {
                return;
            }

            const response = await fetch('api.php?action=update_friend_request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ request_id: requestId, status: status })
            });
            const result = await response.json();
            if (result.success) {
                alert(`Friend request ${status}.`);
                getFriendRequests();
                fetchFriends();
            } else {
                alert(`Error: ${result.error}`);
            }
        });

        async function fetchFriends() {
            const response = await fetch('api.php?action=get_friends');
            const friends = await response.json();
            friendsUl.innerHTML = '';
            friends.forEach(friend => {
                const li = document.createElement('li');
                li.textContent = friend.username;
                li.dataset.userId = friend.id;
                li.addEventListener('click', () => selectUser(friend));
                friendsUl.appendChild(li);
            });
        }

        function selectUser(user) {
            currentRecipientId = user.id;
            chatWithUser.textContent = `Chat with ${user.username}`;
            document.querySelectorAll('#friends-ul li').forEach(li => li.classList.remove('active'));
            document.querySelector(`#friends-ul li[data-user-id='${user.id}']`).classList.add('active');
            
            messageForm.style.display = 'flex';
            chatMessages.innerHTML = '';
            fetchMessages();

            if (messagePollingInterval) clearInterval(messagePollingInterval);
            messagePollingInterval = setInterval(fetchMessages, 3000);
        }

        let currentMessageCount = 0;

        async function fetchMessages(forceScroll = false) {
            if (!currentRecipientId) return;
            
            try {
                const response = await fetch(`api.php?action=get_messages&user_id=${currentRecipientId}`);
                if (!response.ok) throw new Error('Failed to fetch messages.');
                const messages = await response.json();

                if (messages.length === currentMessageCount && !forceScroll) {
                    return; 
                }
                currentMessageCount = messages.length;

                chatMessages.innerHTML = '';
                messages.forEach(msg => {
                    const messageEl = document.createElement('div');
                    messageEl.classList.add('message', msg.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received');
                    
                    let content = '';
                    if (msg.message) {
                        content += `<p>${escapeHTML(msg.message)}</p>`;
                    }
                    if (msg.image_url) {
                        content += `<img src="${msg.image_url}" alt="Image" style="max-width: 100%; border-radius: 10px; margin-top: 5px;">`;
                    }
                    messageEl.innerHTML = content;
                    chatMessages.appendChild(messageEl);
                });

                chatMessages.scrollTop = chatMessages.scrollHeight;

            } catch (error) {
                console.error("Error fetching messages:", error);
            }
        }

        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const messageText = messageInput.value.trim();
            const imageFile = imageInput.files[0];

            if (messageText === '' && !imageFile) return;

            const formData = new FormData();
            formData.append('recipient_id', currentRecipientId);
            formData.append('message', messageText);
            if (imageFile) {
                formData.append('image', imageFile);
            }

            messageInput.value = '';
            imageInput.value = '';

            try {
                const response = await fetch('api.php?action=send_message', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error('Failed to send message.');
                
                await fetchMessages(true);

            } catch (error) {
                console.error("Error sending message:", error);
            }
        });
        
        function escapeHTML(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, 
                tag => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[tag] || tag)
            );
        }

        // Initial data fetch
        getMyUniqueId();
        getFriendRequests();
        fetchFriends();
        setInterval(getFriendRequests, 10000); // Poll for new friend requests every 10 seconds
        <?php endif; ?>

    </script>
</body>
</html>