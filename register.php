<?php
require_once 'config.php';
require_once 'auth.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('index.php');
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Generador de Exámenes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .register-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .btn-gradient {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
            color: white;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .icon-input {
            position: relative;
        }
        .icon-input i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .icon-input input {
            padding-left: 45px;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-card p-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-user-plus fa-3x text-primary"></i>
                        </div>
                        <h2 class="h3 mb-2">Crear Cuenta</h2>
                        <p class="text-muted">Únete para generar exámenes increíbles</p>
                    </div>

                    <!-- Mensaje de alerta -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo htmlspecialchars($message['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de registro -->
                    <form method="POST" id="registerForm">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <div class="icon-input">
                                    <i class="fas fa-user"></i>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <div class="icon-input">
                                    <i class="fas fa-user"></i>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <div class="icon-input">
                                <i class="fas fa-at"></i>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <small class="form-text text-muted">Debe ser único y no contener espacios</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <div class="icon-input">
                                <i class="fas fa-envelope"></i>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="icon-input">
                                <i class="fas fa-lock"></i>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div id="passwordStrength" class="password-strength bg-light"></div>
                            <small class="form-text text-muted">Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números</small>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <div class="icon-input">
                                <i class="fas fa-lock"></i>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                Acepto los términos y condiciones de uso
                            </label>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-gradient btn-lg">
                                <i class="fas fa-user-plus me-2"></i>
                                Crear Cuenta
                            </button>
                        </div>
                    </form>

                    <!-- Link de login -->
                    <div class="text-center">
                        <p class="mb-0">¿Ya tienes una cuenta?</p>
                        <a href="login.php" class="text-decoration-none">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Inicia sesión aquí
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de la fortaleza de la contraseña
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            let color = '';

            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    color = 'bg-danger';
                    break;
                case 2:
                    color = 'bg-warning';
                    break;
                case 3:
                    color = 'bg-info';
                    break;
                case 4:
                case 5:
                    color = 'bg-success';
                    break;
            }

            strengthBar.className = `password-strength ${color}`;
            strengthBar.style.width = (strength * 20) + '%';
        });

        // Validación del formulario
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;

            // Validaciones básicas
            if (!nombre || !apellido || !username || !email || !password) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios');
                return;
            }

            // Validar contraseña
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                document.getElementById('password').focus();
                return;
            }

            // Confirmar contraseña
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                document.getElementById('confirm_password').focus();
                return;
            }

            // Validar términos
            if (!terms) {
                e.preventDefault();
                alert('Debe aceptar los términos y condiciones');
                return;
            }

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, ingrese un email válido');
                document.getElementById('email').focus();
                return;
            }

            // Validar username (sin espacios)
            if (/\s/.test(username)) {
                e.preventDefault();
                alert('El nombre de usuario no puede contener espacios');
                document.getElementById('username').focus();
                return;
            }
        });
    </script>
</body>
</html>