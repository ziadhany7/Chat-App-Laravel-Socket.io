<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Chat App Socket.io</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <style>
            .chat-row {
                margin: 50px;
            }

            ul {
                margin:0;
                padding:0;
                list-style: none;
            }

            ul li {
                padding:8px;
                background: #928787;
                margin-bottom: 10px;
            }

            ul li:nth-child(2n-2) {
                background: #c3c5c5;
            }

            .chat-input {
                border: 1px solid lightblue;
                border-top-right-radius: 10px;
                border-top-left-radius: 10px;
                padding: 8px 10px;
                color:#fff;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="chat-content">
                    <ul>
                        {{-- <li>
                            Testttttttttttt message
                        </li> --}}
                    </ul>
                </div>
                <div class="chat-section">
                    <div class="chat-box">
                        <div class="chat-input bg-primary" id="chatInput" contenteditable="">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
        <script src="https://cdn.socket.io/4.8.1/socket.io.min.js" integrity="sha384-mkQ3/7FUtcGyoppY6bz/PORYoGqOl7/aSUMn2ymDOJcapfS6PHqxhRTMh1RR0Q6+" crossorigin="anonymous"></script>
        <script>
            $(function(){
                let ip_address = '127.0.0.1';
                let socket_port = '3000';
                let socket = io(ip_address = ':' + socket_port);

                let chatInput = $('#chatInput');
                chatInput.keypress(function(e){
                    // let message = chatInput.text();
                    let message = $(this).html();
                    console.log(message);
                    if(e.which === 13 && !e.shiftKey){
                        // alert('Message sent :'= message);
                        // alert(1);
                        socket.emit('sendChatToServer', message);
                        chatInput.html('');
                        return false;
                    }
                });

                socket.on('sendChatToClient', function(message){
                    // alert(message);
                    // $('.chat-content ul').append('<li>' + message + '</li>');
                    // $('.chat-content').scrollTop($('.chat-content')[0].scrollHeight);
                    $('.chat-content ul').append('<li>' + message + '</li>');
                });

                // socket.on('connection');
            });
        </script>
    </body>
</html>
