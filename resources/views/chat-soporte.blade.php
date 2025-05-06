@section('content')
<div class="d-flex">
    @include('menu')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h3>Chat Soporte IA</h3>
                    </div>
                    <div class="card-body chat-container">
                        <ul id="chatMessages" class="list-unstyled"></ul>
                    </div>
                    <div class="card-footer">
                        <form id="chatForm" onsubmit="sendMessage(); return false;">
                            <div class="input-group">
                                <input type="text" id="chatInput" class="form-control" placeholder="Escribe tu mensaje...">
                                <button type="submit" class="btn btn-primary ms-2">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;

        appendMessage(message, true);
        input.value = '';

        fetch('{{ url("/consultar-soporte") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    mensaje: message
                })
            })
            .then(res => res.json())
            .then(data => {
                const respuesta = data.message || 'Sin respuesta de la IA.';
                appendMessage(respuesta, false);
            })
            .catch(() => {
                appendMessage('Error al contactar a la IA.', false);
            });
    }

    function appendMessage(text, isUser) {
        const chat = document.getElementById('chatMessages');
        const li = document.createElement('li');
        li.classList.add('chat-message', isUser ? 'user-message' : 'bot-message');
        li.innerText = text;
        chat.appendChild(li);
        li.scrollIntoView({
            behavior: 'smooth'
        });
    }
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
        text-align: left;
    }
</style>
@endsection