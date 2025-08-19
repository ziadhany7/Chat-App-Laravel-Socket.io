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

  socket.on('chat:message', (data) => {
    const messageData = {
      user: data.user || 'Anonymous',
      message: data.message,
      time: new Date().toLocaleTimeString()
    };
    // Send to everyone except sender
    socket.broadcast.emit('chat:message', messageData);
    console.log(`[${messageData.time}] ${messageData.user}: ${messageData.message}`);
  });

  socket.on('disconnect', () => {
    console.log(`User disconnected: ${socket.id}`);
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
