<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Registrar</title>
    <style>
        body {
            background-color: #f7f7f7;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .container {
            padding-top: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center bg-primary text-white">
                        <h3>Registrar</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" required>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100" disabled>Registrar</button>
                            </div>
                        </form>
                        <div class="text-center mt-2">
                            <a href="{{ route('login') }}" class="btn btn-link">Regresar a Iniciar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');
            const submitButton = document.querySelector('button[type="submit"]');

            function validatePassword() {
                if (password.value === passwordConfirm.value && password.value !== '') {
                    submitButton.disabled = false;
                } else {
                    submitButton.disabled = true;
                }
            }

            password.addEventListener('input', validatePassword);
            passwordConfirm.addEventListener('input', validatePassword);
        });
    </script>
</body>

</html>