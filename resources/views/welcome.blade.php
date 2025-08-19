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
    .message { padding: 6px; border-radius: 5px; margin-bottom: 5px; position: relative; }
    .message.user { background-color: #d1e7dd; }
    .message.other { background-color: #f8d7da; }
    .time { font-size: 0.8rem; color: #6c757d; float: right; }
    .seen { font-size: 0.7rem; color: blue; position: absolute; bottom: 2px; right: 5px; }
    .chat-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; position: relative; }
    .chat-item:hover { background: #f0f0f0; }
    .badge-unread { position: absolute; top: 10px; right: 10px; background: red; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- القائمة الجانبية -->
    <div class="col-3 sidebar">
      <h5 class="p-2">Chats</h5>
      <div id="chatList"></div>
      <hr>
      <h6 class="p-2">Online Users</h6>
      <div id="userList"></div>
    </div>

    <!-- نافذة المحادثة -->
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
  const unreadCounts = {}; // roomName -> count

  function joinRoom(room) {
    currentRoom = room;
    unreadCounts[room] = 0; // تصفير العداد عند فتح الشات
    updateUnreadUI();
    $('#currentRoom').text("Room: " + room);
    chatBox.html('');
    socket.emit('joinRoom', room);
    // إرسال إشعار السيرفر بأننا قرأنا الرسائل
    socket.emit('messages:seen', { room: room, user: username });
  }

  chatInput.keypress(function(e) {
    if (e.which === 13 && this.value.trim() !== '' && currentRoom) {
      socket.emit('chat:message', { user: username, message: this.value, room: currentRoom });
      this.value = '';
      return false;
    }
  });

  socket.on('chat:message', function(data) {
    if (currentRoom === data.room) {
        // فقط لو الرسالة تخص الغرفة الحالية يتم عرضها
        appendMessage(data.id, data.user, data.message, data.user === username, data.time, data.seenBy);
        socket.emit('messages:seen', { room: data.room, user: username });
    } else {
        // لو الرسالة ليست في الغرفة الحالية => زيادة عداد الغرفة فقط
        unreadCounts[data.room] = (unreadCounts[data.room] || 0) + 1;
        updateUnreadUI();
    }
});


  socket.on('chat:history', function(messages) {
    chatBox.html('');
    messages.forEach(msg => {
      appendMessage(msg.id, msg.user, msg.message, msg.user === username, msg.time, msg.seenBy);
    });
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
    $('#userList .chat-item').off('click').on('click', function() {
      joinRoom($(this).data('room'));
    });
  });

  // قائمة الرومات العامة
  chatList.html(`
    <div class="chat-item" data-room="general">General Group</div>
    <div class="chat-item" data-room="team">Team Group</div>
  `);
  $('#chatList .chat-item').on('click', function() {
    joinRoom($(this).data('room'));
  });

  // تحديث حالة القراءة
  socket.on('messages:seen:update', function(data) {
    // تعديل الرسائل المعروضة وإضافة "Seen" عند الحاجة
    data.seenBy.forEach(msgId => {
      $(`#msg-${msgId}`).find('.seen').remove();
      $(`#msg-${msgId}`).append('<span class="seen">Seen</span>');
    });
  });
// socket.on('messages:seen:update', function(data) {
//   data.updatedMessages.forEach(msg => {
//     const seenText = msg.seenBy.length > 0 ? 'Seen by: ' + msg.seenBy.join(', ') : '';
//     $(`#msg-${msg.id}`).find('.seen').remove();
//     if (seenText) {
//       $(`#msg-${msg.id}`).append(`<span class="seen">${seenText}</span>`);
//     }
//   });
// });


  function appendMessage(id, user, message, isOwn, time, seenBy=[]) {
    const sideClass = isOwn ? 'user' : 'other';
    const seenMark = (isOwn && seenBy && seenBy.length > 0) ? '<span class="seen">Seen</span>' : '';
    const html = `<div class="message ${sideClass}" id="msg-${id}">
                    <strong>${user}:</strong> ${$('<div>').text(message).html()}
                    <span class="time">${time}</span>
                    ${seenMark}
                  </div>`;
    chatBox.append(html);
    chatBox.scrollTop(chatBox[0].scrollHeight);
  }

  function updateUnreadUI() {
    $('.badge-unread').remove();
    Object.keys(unreadCounts).forEach(room => {
      if (unreadCounts[room] > 0) {
        $(`[data-room="${room}"]`).append(`<span class="badge-unread">${unreadCounts[room]}</span>`);
      }
    });
  }

  // لو السيرفر طلب الانضمام القسري لغرفة
  socket.on('forceJoin', function(room) {
    joinRoom(room);
  });
});
</script>
</body>
</html>
