<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Advanced Chat App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { overflow: hidden; }
    .sidebar { height: 100vh; border-right: 1px solid #ccc; overflow-y: auto; }
    .chat-box { height: 80vh; overflow-y: auto; border: 1px solid #ccc; padding: 10px; }
    .chat-input { width: 100%; padding: 8px; }
    .message { padding: 6px; border-radius: 5px; margin-bottom: 5px; }
    .message.user { background-color: #d1e7dd; }
    .message.other { background-color: #f8d7da; }
    .time { font-size: 0.8rem; color: #6c757d; float: right; }
    .chat-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
    .chat-item:hover { background: #f0f0f0; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- قائمة الشاتات -->
    <div class="col-3 sidebar">
      <h5 class="p-2">Chats</h5>
      <div id="chatList"></div>
      <hr>
      <h6 class="p-2">Online Users</h6>
      <div id="userList"></div>
    </div>

    <!-- منطقة المحادثة -->
    <div class="col-9">
      <h4 class="p-2" id="currentRoom">No Room</h4>
      <div class="chat-box mb-3" id="chatBox"></div>
      <input type="text" class="form-control chat-input" id="chatInput" placeholder="Type message and hit Enter">
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.socket.io/4.8.1/socket.io.min.js"></script>
<script>
$(function() {
  const username = prompt("Enter your name:") || "Anonymous";
  const socket = io('http://127.0.0.1:3000');
  socket.emit('register', username);

  let currentRoom = null;
  const chatBox = $('#chatBox');
  const chatInput = $('#chatInput');
  const chatList = $('#chatList');
  const userList = $('#userList');

  // انضمام إلى روم جديد
  function joinRoom(room) {
    currentRoom = room;
    $('#currentRoom').text("Room: " + room);
    chatBox.html('');
    socket.emit('joinRoom', room);
  }

  // إرسال الرسائل
  chatInput.keypress(function(e) {
    if (e.which === 13 && this.value.trim() !== '' && currentRoom) {
      socket.emit('chat:message', { user: username, message: this.value, room: currentRoom });
      this.value = '';
      return false;
    }
  });

  // استقبال الرسائل
  socket.on('chat:message', function(data) {
    appendMessage(data.user, data.message, data.user === username, data.time);
  });

  socket.on('system', function(msg) {
    chatBox.append(`<div class="text-center text-muted"><em>${msg}</em></div>`);
  });

    socket.on('userList', function(users) {
    userList.html('');
    users.forEach(u => {
        if (u !== username) {
        const sorted = [username, u].sort();
        const id = `priv-${sorted[0]}-${sorted[1]}`.replace(/\s+/g, '_');
        userList.append(`<div class="chat-item" data-room="${id}">${u}</div>`);
        }
    });
    // إضافة كليك لفتح شات خاص
    $('#userList .chat-item').off('click').on('click', function() {
        joinRoom($(this).data('room'));
    });
    });


  // إضافة روم جروب يدوي
  chatList.html(`
    <div class="chat-item" data-room="general">General Group</div>
    <div class="chat-item" data-room="team">Team Group</div>
  `);
  $('#chatList .chat-item').on('click', function() {
    joinRoom($(this).data('room'));
  });

  function appendMessage(user, message, isOwn, time) {
    const sideClass = isOwn ? 'user' : 'other';
    const html = `<div class="message ${sideClass}">
                    <strong>${user}:</strong> ${$('<div>').text(message).html()}
                    <span class="time">${time}</span>
                  </div>`;
    chatBox.append(html);
    chatBox.scrollTop(chatBox[0].scrollHeight);
  }
});
</script>
</body>
</html>
