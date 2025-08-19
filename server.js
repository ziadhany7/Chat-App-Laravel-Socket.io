require('dotenv').config();
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const server = http.createServer(app);

const io = new Server(server, {
  cors: { origin: '*' }
});

io.on('connection', (socket) => {
  console.log(`User connected: ${socket.id}`);

  // المستخدم يختار الروم
  socket.on('joinRoom', (roomName) => {
    socket.join(roomName);
    console.log(`${socket.id} joined room: ${roomName}`);
    socket.emit('system', `You joined room: ${roomName}`);
  });

  // إرسال الرسائل داخل الروم فقط
  socket.on('chat:message', (data) => {
    const messageData = {
      user: data.user || 'Anonymous',
      message: data.message,
      time: new Date().toLocaleTimeString()
    };
    io.to(data.room).emit('chat:message', messageData);
    console.log(`[${messageData.time}] (${data.room}) ${messageData.user}: ${messageData.message}`);
  });

  socket.on('disconnect', () => {
    console.log(`User disconnected: ${socket.id}`);
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
