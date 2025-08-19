require('dotenv').config();
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { randomUUID } = require('crypto');

const app = express();
const server = http.createServer(app);
const io = new Server(server, { cors: { origin: '*' } });

let users = {};                // socket.id -> username
let roomMessages = {};         // roomName -> [ { id, user, message, time, seenBy: [] } ]

io.on('connection', (socket) => {
  console.log(`User connected: ${socket.id}`);

  socket.on('register', (username) => {
    users[socket.id] = username;
    io.emit('userList', Object.values(users));
  });

  socket.on('joinRoom', (roomName) => {
    socket.join(roomName);
    socket.emit('system', `You joined room: ${roomName}`);
    console.log(`${users[socket.id] || socket.id} joined ${roomName}`);

    if (roomMessages[roomName]) {
      socket.emit('chat:history', roomMessages[roomName]);
    }
  });

  socket.on('chat:message', (data) => {
    const msgId = randomUUID();
    const messageData = {
      id: msgId,
      user: data.user || 'Anonymous',
      message: data.message,
      time: new Date().toLocaleTimeString(),
      room: data.room,
      seenBy: []
    };

    if (!roomMessages[data.room]) roomMessages[data.room] = [];
    roomMessages[data.room].push(messageData);

    io.to(data.room).emit('chat:message', messageData);
    console.log(`[${messageData.time}] (${data.room}) ${messageData.user}: ${messageData.message}`);

    // const targetUserId = Object.keys(users)
    //   .find(id => users[id] !== data.user && data.room.includes(users[id]));
    // if (targetUserId) {
    //   io.to(targetUserId).emit('forceJoin', data.room);
    // }
  });

  // تحديث حالة Seen
  socket.on('messages:seen', (data) => {
    const { room, user } = data;
    if (!roomMessages[room]) return;

    const updatedIds = [];
    roomMessages[room].forEach(msg => {
      if (msg.user !== user && !msg.seenBy.includes(user)) {
        msg.seenBy.push(user);
        updatedIds.push(msg.id);
      }
    });

    if (updatedIds.length > 0) {
      // إخطار جميع الموجودين بالغرفة
    //   io.to(room).emit('messages:seen:update', { room: room, seenBy: updatedIds });
        io.to(room).emit('messages:seen:update', { room: room, updatedMessages: roomMessages[room] });

    }
  });

  socket.on('disconnect', () => {
    delete users[socket.id];
    io.emit('userList', Object.values(users));
    console.log(`User disconnected: ${socket.id}`);
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => console.log(`Server running on port ${PORT}`));
