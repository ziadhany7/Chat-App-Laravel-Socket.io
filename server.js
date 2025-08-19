require('dotenv').config();
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*' } });

let users = {}; // socket.id -> username

io.on('connection', (socket) => {
  console.log(`User connected: ${socket.id}`);

  // تسجيل المستخدم
  socket.on('register', (username) => {
    users[socket.id] = username;
    io.emit('userList', Object.values(users)); // إرسال قائمة الأونلاين
  });

  // الانضمام لغرفة
  socket.on('joinRoom', (roomName) => {
    socket.join(roomName);
    socket.emit('system', `You joined room: ${roomName}`);
    console.log(`${users[socket.id] || socket.id} joined ${roomName}`);
  });

  // استقبال وإرسال الرسائل
  socket.on('chat:message', (data) => {
    const messageData = {
      user: data.user || 'Anonymous',
      message: data.message,
      time: new Date().toLocaleTimeString()
    };
    io.to(data.room).emit('chat:message', messageData);
    console.log(`[${messageData.time}] (${data.room}) ${messageData.user}: ${messageData.message}`);

    // إجبار الطرف الآخر على دخول نفس الغرفة
    const targetUserId = Object.keys(users)
      .find(id => users[id] !== data.user && data.room.includes(users[id]));
    if (targetUserId) {
      io.to(targetUserId).emit('forceJoin', data.room);
    }
  });

  // عند الخروج
  socket.on('disconnect', () => {
    delete users[socket.id];
    io.emit('userList', Object.values(users));
    console.log(`User disconnected: ${socket.id}`);
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => console.log(`Server running on port ${PORT}`));
