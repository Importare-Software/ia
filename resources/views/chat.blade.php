@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h3>Chatbot</h3>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="incognitoMode" onchange="toggleIncognitoMode()">
                            <label class="form-check-label" for="incognitoMode">Modo Incógnito</label>
                        </div>
                    </div>
                    <div class="card-body chat-container">
                        <ul id="chatMessages" class="list-unstyled"></ul>
                    </div>
                    <div class="card-footer">
                        <form id="chatForm" onsubmit="sendMessage(); return false;">
                            <div class="input-group">
                                <input type="text" id="chatInput" class="form-control" placeholder="Escribe tu mensaje...">
                                <button type="submit" class="btn btn-primary" style="margin-left: 5px">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let incognito = false;

    function toggleIncognitoMode() {
        incognito = !incognito;
    }

    function sendMessage() {
        const message = document.getElementById('chatInput').value;
        if (!message.trim()) return;

        const session_id = '{{ session()->getId() }}';

        fetch('{{ route('chat.sendMessage') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: session_id,
                        incognito: incognito
                    })
                })
            .then(response => response.json())
            .then(data => {
                document.getElementById('chatInput').value = '';
                appendMessage(message, true);
                appendMessage(data.message, false);
            });
    }

    function appendMessage(message, isUser) {
        const chatMessages = document.getElementById('chatMessages');
        const messageElement = document.createElement('li');
        messageElement.classList.add('chat-message', isUser ? 'user-message' : 'bot-message');
        messageElement.textContent = message;
        chatMessages.appendChild(messageElement);
        messageElement.scrollIntoView({ behavior: 'smooth' });
    }

    function loadMessages() {
        const session_id = '{{ session()->getId() }}';

        fetch('{{ route('chat.getMessages') }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        session_id: session_id,
                        incognito: incognito
                    })
                })
            .then(response => response.json())
            .then(data => {
                data.forEach(msg => {
                    appendMessage(msg.message, msg.is_user);
                });
            });
    }

    document.addEventListener('DOMContentLoaded', loadMessages);
</script>

<style>
    .chat-container {
        height: 400px;
        overflow-y: auto;
        border-bottom: 1px solid #ddd;
    }

    .chat-message {
        padding: 10px;
        margin: 5px 0;
        border-radius: 10px;
    }

    .user-message {
        background-color: #d1e7dd;
        text-align: right;
    }

    .bot-message {
        background-color: #f8d7da;
    }
</style>
@endsection
