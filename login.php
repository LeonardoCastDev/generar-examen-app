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
    <title>Login - Generador de Exámenes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .auth-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .form-floating > label {
            color: #6c757d;
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
        .logo {
            font-size: 3rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-switch {
            margin-top: 1rem;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        .form-floating {
            position: relative;
        }
        .password-strength {
            margin-top: 0.5rem;
        }
        .progress {
            height: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <!-- Mensaje de alerta -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-4">
                    <!-- Logo y título -->
                    <div class="text-center mb-4">
                        <i class="fas fa-graduation-cap logo"></i>
                        <h2 class="mt-2 mb-1">Generador de Exámenes</h2>
                        <p class="text-muted">Inicia sesión para comenzar</p>
                    </div>

                    <!-- Toggle entre Login y Registro -->
                    <div class="form-check form-switch text-center mb-4">
                        <input class="form-check-input" type="checkbox" id="authToggle">
                        <label class="form-check-label" for="authToggle" id="toggleLabel">
                            ¿No tienes cuenta? Regístrate
                        </label>
                    </div>

                    <!-- Formulario de Login -->
                    <form id="loginForm" method="POST">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="loginUsername" name="username" placeholder="Usuario o Email" required>
                            <label for="loginUsername">
                                <i class="fas fa-user me-2"></i>Usuario o Email
                            </label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Contraseña" required>
                            <label for="loginPassword">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('loginPassword', this)"></i>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-gradient btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>
                        </div>
                    </form>

                    <!-- Formulario de Registro -->
                    <form id="registerForm" method="POST" style="display: none;">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
                                    <label for="nombre">
                                        <i class="fas fa-user me-2"></i>Nombre
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido">
                                    <label for="apellido">
                                        <i class="fas fa-user me-2"></i>Apellido
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Usuario">
                            <label for="username">
                                <i class="fas fa-at me-2"></i>Nombre de Usuario
                            </label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña">
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Contraseña
                            </label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)"></i>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirmar Contraseña">
                            <label for="confirmPassword">
                                <i class="fas fa-lock me-2"></i>Confirmar Contraseña
                            </label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirmPassword', this)"></i>
                        </div>

                        <!-- Indicador de fortaleza de contraseña -->
                        <div class="password-strength mb-3">
                            <div class="progress">
                                <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="passwordStrengthText" class="text-muted">Ingresa una contraseña</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-gradient btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4">
                <p class="text-white-50">
                    <i class="fas fa-shield-alt me-1"></i>
                    Sistema seguro de gestión de exámenes
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle entre login y registro
        document.getElementById('authToggle').addEventListener('change', function() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const toggleLabel = document.getElementById('toggleLabel');
            
            if (this.checked) {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                toggleLabel.textContent = '¿Ya tienes cuenta? Inicia sesión';
            } else {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                toggleLabel.textContent = '¿No tienes cuenta? Regístrate';
            }
        });

        // Función para mostrar/ocultar contraseña
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validación del formulario de registro
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            
            // Validar campos requeridos
            if (!nombre || !apellido || !username || !email || !password) {
                e.preventDefault();
                alert('Por favor completa todos los campos');
                return;
            }
            
            // Validar coincidencia de contraseñas
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
            
            // Validar longitud de contraseña
            if (password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor ingresa un email válido');
                return;
            }
            
            // Validar username (solo letras, números y guiones bajos)
            const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
            if (!usernameRegex.test(username)) {
                e.preventDefault();
                alert('El nombre de usuario debe tener entre 3 y 20 caracteres y solo puede contener letras, números y guiones bajos');
                return;
            }
        });

        // Validación del formulario de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('loginUsername').value.trim();
            const password = document.getElementById('loginPassword').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Por favor completa todos los campos');
                return;
            }
            
            // Mostrar loading
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Iniciando sesión...';
            btn.disabled = true;
            
            // Restaurar después de 10 segundos si hay error
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 10000);
        });

        // Verificar fortaleza de contraseña en tiempo real
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');
            
            let strength = 0;
            let text = 'Muy débil';
            let color = 'danger';
            
            // Criterios de fortaleza
            if (password.length >= 6) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
            
            const percentage = (strength / 5) * 100;
            
            // Determinar texto y color
            if (strength <= 1) {
                text = 'Muy débil';
                color = 'danger';
            } else if (strength <= 2) {
                text = 'Débil';
                color = 'warning';
            } else if (strength <= 3) {
                text = 'Media';
                color = 'info';
            } else if (strength <= 4) {
                text = 'Fuerte';
                color = 'success';
            } else {
                text = 'Muy fuerte';
                color = 'success';
            }
            
            strengthBar.style.width = percentage + '%';
            strengthBar.className = `progress-bar bg-${color}`;
            strengthText.textContent = text;
            strengthText.className = `text-${color}`;
        });

        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.transform = 'translateY(20px)';
            card.style.opacity = '0';
            
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.transform = 'translateY(0)';
                card.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>