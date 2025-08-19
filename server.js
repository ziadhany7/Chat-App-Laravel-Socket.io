const express = require('express');
const { Socket } = require('socket.io');
const app = express();

const server = require('http').createServer(app);

const io =  require('socket.io')(server, {
    cors: {origin: '*'}
});
io.on('connection', (Socket)=>{
    console.log("connection");

    Socket.on('sendChatToServer', (message) => {
        console.log(message);

        // io.sockets.emit('sendChatToClient', message);

        Socket.broadcast.emit('sendChatToClient', message);
    });


    Socket.on('disconnect',(socket)=>{
        console.log('Disconnected');
    });
});
server.listen(3000,()=>{
    console.log("server is running now");

});
