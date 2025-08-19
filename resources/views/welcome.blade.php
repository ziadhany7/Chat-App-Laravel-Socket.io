<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Professional Chat App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .chat-box { max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; }
    .chat-input { width: 100%; padding: 8px; }
    .message { padding: 6px; border-radius: 5px; margin-bottom: 5px; }
    .message.user { background-color: #d1e7dd; }
    .message.other { background-color: #f8d7da; }
    .time { font-size: 0.8rem; color: #6c757d; float: right; }
  </style>
</head>
<body>
  <div class="container mt-4">
    <h3>Socket.IO Chat</h3>
    <div class="chat-box mb-3" id="chatBox"></div>
    <input type="text" class="form-control chat-input" id="chatInput" placeholder="Type a message and hit Enter">
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.socket.io/4.8.1/socket.io.min.js"></script>
  <script>
    $(function() {
      const username = prompt("Enter your name:") || "Anonymous";
      const room = prompt("Enter room name to join:") || "general";

      const socket = io('http://127.0.0.1:3000');
      socket.emit('joinRoom', room);

      const chatBox = $('#chatBox');
      const chatInput = $('#chatInput');

      chatInput.keypress(function(e) {
        if (e.which === 13 && this.value.trim() !== '') {
          socket.emit('chat:message', { user: username, message: this.value, room });
          this.value = '';
          return false;
        }
      });

      socket.on('chat:message', function(data) {
        appendMessage(data.user, data.message, data.user === username, data.time);
      });

      socket.on('system', function(msg) {
        chatBox.append(`<div class="text-center text-muted"><em>${msg}</em></div>`);
      });

      function appendMessage(user, message, isOwn, time = new Date().toLocaleTimeString()) {
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
