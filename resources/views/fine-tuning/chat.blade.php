@extends('layouts.app')

@section('content')
<div class="d-flex">
    @include('menu')
    <div class="chat-window">
        <div class="chat-header">
            <h3>Chatbot Fine-tuning</h3>
        </div>
        <div id="chat-content" class="chat-content">
            <!-- Los mensajes del chat aparecerán aquí -->
        </div>
        <form id="chat-form">
            <div class="input-group">
                <input type="text" id="message" name="message" class="form-control" placeholder="Escribe tu mensaje aquí..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>
</div>

<!-- Incluir jQuery (versión completa) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Incluir Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Función para agregar mensajes al chat
        function addMessage(content, sender) {
            var messageHtml = '<div class="message ' + sender + '"><div class="message-content">' + content + '</div></div>';
            $('#chat-content').append(messageHtml);
            $('#chat-content').scrollTop($('#chat-content')[0].scrollHeight);
        }

        // Manejar el envío del formulario
        $('#chat-form').on('submit', function(e) {
            e.preventDefault();

            var message = $('#message').val();

            if (message.trim() === '') {
                return;
            }

            // Mostrar el mensaje del usuario en el chat
            addMessage(message, 'user');

            // Limpiar el campo de entrada
            $('#message').val('');
            // Enviar el mensaje al servidor
            $.ajax({
                url: '{{ route("chat.send") }}',
                method: 'POST',
                headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                data: JSON.stringify({ message: message }),
                success: function(response) {
                    // Mostrar la respuesta del bot en el chat
                    addMessage(response.response, 'bot');
                },
                error: function() {
                    // Manejar errores
                    addMessage('Hubo un error al procesar tu solicitud. Por favor, intenta nuevamente.', 'bot');
                }
            });
        });
    });
</script>

<style>
    body {
        background-color: #f8f9fa;
    }

    .chat-window {
        width: 800px;
        margin: 50px auto;
        background-color: #ffffff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .chat-header {
        text-align: center;
        margin-bottom: 20px;
    }

    .chat-content {
        height: 400px;
        overflow-y: scroll;
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background-color: #e9ecef;
    }

    .message {
        margin-bottom: 15px;
    }

    .message.user .message-content {
        background-color: #007bff;
        color: #ffffff;
        text-align: right;
        float: right;
        clear: both;
    }

    .message.bot .message-content {
        background-color: #f1f0f0;
        color: #000000;
        text-align: left;
        float: left;
        clear: both;
    }

    .message-content {
        display: inline-block;
        padding: 10px 15px;
        border-radius: 20px;
        max-width: 70%;
    }

    .input-group {
        margin-top: 20px;
    }
</style>
@endsection