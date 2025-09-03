<?php
require_once 'config.php';
require_once 'ExamGenerator.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    redirect('login.php');
}

$examGenerator = new ExamGenerator();
$user = getCurrentUser();

// Procesar formulario de agregar pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_pregunta'])) {
    $opciones = null;
    $respuestas = null;
    
    // Procesar opciones para preguntas múltiples
    if ($_POST['tipo_pregunta'] === 'multiple') {
        $opciones = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_POST["opcion_$i"])) {
                $opciones[] = [
                    'letra' => chr(64 + $i), // A, B, C, D
                    'texto' => $_POST["opcion_$i"],
                    'correcta' => isset($_POST['respuesta_correcta']) && $_POST['respuesta_correcta'] == $i
                ];
            }
        }
    }
    
    // Procesar respuestas para preguntas abiertas
    if ($_POST['tipo_pregunta'] === 'abierta') {
        $respuestas = [];
        if (!empty($_POST['respuesta_abierta'])) {
            $respuestas[] = [
                'texto' => $_POST['respuesta_abierta'],
                'exacta' => isset($_POST['respuesta_exacta'])
            ];
        }
    }
    
    // Procesar respuestas para verdadero/falso
    if ($_POST['tipo_pregunta'] === 'verdadero_falso') {
        $respuestas = [];
        if (!empty($_POST['respuesta_vf'])) {
            $respuestas[] = [
                'texto' => $_POST['respuesta_vf'],
                'exacta' => true
            ];
        }
    }
    
    $result = $examGenerator->agregarPregunta(
        1, // ID materia ADMISION (siempre será 1)
        $_POST['enunciado'],
        $_POST['tipo_pregunta'],
        $_POST['nivel_dificultad'],
        $_POST['puntuacion'],
        $opciones,
        $respuestas
    );
    
    showMessage($result['message'], $result['success'] ? 'success' : 'error');
    
    if ($result['success']) {
        // Limpiar formulario después del éxito
        unset($_POST);
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Pregunta - Generador de Exámenes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .option-input {
            margin-bottom: 10px;
        }
        .correct-option {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .question-preview {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg gradient-bg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Volver al Inicio
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="mis_examenes.php"><i class="fas fa-file-alt me-2"></i>Mis Exámenes</a></li>
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i>Inicio</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Mensaje de alerta -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="text-center">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-plus-circle text-success me-3"></i>
                        Agregar Nueva Pregunta
                    </h1>
                    <p class="lead text-muted">Añade preguntas al banco para ADMISION</p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow card-hover">
                    <div class="card-header gradient-bg">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Formulario de Nueva Pregunta
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="preguntaForm">
                            <!-- Enunciado -->
                            <div class="mb-3">
                                <label for="enunciado" class="form-label">
                                    <i class="fas fa-question-circle me-1"></i>
                                    Enunciado de la Pregunta *
                                </label>
                                <textarea class="form-control" id="enunciado" name="enunciado" rows="3" required 
                                          placeholder="Escribe aquí la pregunta..."></textarea>
                            </div>

                            <div class="row">
                                <!-- Tipo de Pregunta -->
                                <div class="col-md-4 mb-3">
                                    <label for="tipo_pregunta" class="form-label">
                                        <i class="fas fa-list me-1"></i>
                                        Tipo de Pregunta *
                                    </label>
                                    <select class="form-select" id="tipo_pregunta" name="tipo_pregunta" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="multiple">Opción Múltiple</option>
                                        <option value="verdadero_falso">Verdadero/Falso</option>
                                        <option value="abierta">Respuesta Abierta</option>
                                    </select>
                                </div>

                                <!-- Nivel de Dificultad -->
                                <div class="col-md-4 mb-3">
                                    <label for="nivel_dificultad" class="form-label">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Dificultad *
                                    </label>
                                    <select class="form-select" id="nivel_dificultad" name="nivel_dificultad" required>
                                        <option value="facil">Fácil</option>
                                        <option value="medio" selected>Medio</option>
                                        <option value="dificil">Difícil</option>
                                    </select>
                                </div>

                                <!-- Puntuación -->
                                <div class="col-md-4 mb-3">
                                    <label for="puntuacion" class="form-label">
                                        <i class="fas fa-star me-1"></i>
                                        Puntuación *
                                    </label>
                                    <input type="number" class="form-control" id="puntuacion" name="puntuacion" 
                                           min="0.1" max="10" step="0.1" value="1.0" required>
                                </div>
                            </div>

                            <!-- Opciones para Pregunta Múltiple -->
                            <div id="opciones_multiple" style="display: none;">
                                <h5 class="mb-3">
                                    <i class="fas fa-list-ul me-2"></i>
                                    Opciones de Respuesta
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="option-input">
                                            <label class="form-label">Opción A *</label>
                                            <input type="text" class="form-control" name="opcion_1" placeholder="Primera opción">
                                        </div>
                                        <div class="option-input">
                                            <label class="form-label">Opción B *</label>
                                            <input type="text" class="form-control" name="opcion_2" placeholder="Segunda opción">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="option-input">
                                            <label class="form-label">Opción C *</label>
                                            <input type="text" class="form-control" name="opcion_3" placeholder="Tercera opción">
                                        </div>
                                        <div class="option-input">
                                            <label class="form-label">Opción D *</label>
                                            <input type="text" class="form-control" name="opcion_4" placeholder="Cuarta opción">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Respuesta Correcta *
                                    </label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="respuesta_correcta" value="1" id="correcta_1">
                                                <label class="form-check-label" for="correcta_1">Opción A</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="respuesta_correcta" value="2" id="correcta_2">
                                                <label class="form-check-label" for="correcta_2">Opción B</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="respuesta_correcta" value="3" id="correcta_3">
                                                <label class="form-check-label" for="correcta_3">Opción C</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="respuesta_correcta" value="4" id="correcta_4">
                                                <label class="form-check-label" for="correcta_4">Opción D</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones para Verdadero/Falso -->
                            <div id="opciones_vf" style="display: none;">
                                <h5 class="mb-3">
                                    <i class="fas fa-check-double me-2"></i>
                                    Respuesta Correcta
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="respuesta_vf" value="Verdadero" id="vf_verdadero">
                                            <label class="form-check-label" for="vf_verdadero">
                                                <i class="fas fa-check text-success me-1"></i>
                                                Verdadero
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="respuesta_vf" value="Falso" id="vf_falso">
                                            <label class="form-check-label" for="vf_falso">
                                                <i class="fas fa-times text-danger me-1"></i>
                                                Falso
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opciones para Pregunta Abierta -->
                            <div id="opciones_abierta" style="display: none;">
                                <h5 class="mb-3">
                                    <i class="fas fa-edit me-2"></i>
                                    Respuesta Correcta
                                </h5>
                                <div class="mb-3">
                                    <label for="respuesta_abierta" class="form-label">Respuesta modelo *</label>
                                    <textarea class="form-control" name="respuesta_abierta" rows="3" 
                                              placeholder="Escribe la respuesta correcta o modelo..."></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="respuesta_exacta" id="respuesta_exacta">
                                    <label class="form-check-label" for="respuesta_exacta">
                                        La respuesta debe ser exacta (sin variaciones)
                                    </label>
                                </div>
                            </div>

                            <!-- Vista previa -->
                            <div id="preview" class="question-preview" style="display: none;">
                                <h6><i class="fas fa-eye me-2"></i>Vista Previa</h6>
                                <div id="preview-content"></div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="previewQuestion()">
                                    <i class="fas fa-eye me-1"></i>
                                    Vista Previa
                                </button>
                                <button type="button" class="btn btn-outline-warning me-md-2" onclick="clearForm()">
                                    <i class="fas fa-eraser me-1"></i>
                                    Limpiar
                                </button>
                                <button type="submit" name="agregar_pregunta" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>
                                    Guardar Pregunta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cambiar opciones según el tipo de pregunta
        document.getElementById('tipo_pregunta').addEventListener('change', function() {
            const tipo = this.value;
            const opcionesMultiple = document.getElementById('opciones_multiple');
            const opcionesVF = document.getElementById('opciones_vf');
            const opcionesAbierta = document.getElementById('opciones_abierta');

            // Ocultar todas las opciones
            opcionesMultiple.style.display = 'none';
            opcionesVF.style.display = 'none';
            opcionesAbierta.style.display = 'none';

            // Mostrar las opciones correspondientes
            if (tipo === 'multiple') {
                opcionesMultiple.style.display = 'block';
                // Hacer requeridos los campos de opciones múltiples
                document.querySelectorAll('input[name^="opcion_"]').forEach(input => {
                    input.required = true;
                });
            } else if (tipo === 'verdadero_falso') {
                opcionesVF.style.display = 'block';
            } else if (tipo === 'abierta') {
                opcionesAbierta.style.display = 'block';
                // Hacer requerido el campo de respuesta abierta
                document.querySelector('textarea[name="respuesta_abierta"]').required = true;
            }
        });

        // Vista previa de la pregunta
        function previewQuestion() {
            const enunciado = document.getElementById('enunciado').value;
            const tipo = document.getElementById('tipo_pregunta').value;
            const preview = document.getElementById('preview');
            const previewContent = document.getElementById('preview-content');

            if (!enunciado.trim()) {
                alert('Por favor, escribe el enunciado de la pregunta');
                return;
            }

            let content = `<strong>Pregunta:</strong> ${enunciado}<br><br>`;

            if (tipo === 'multiple') {
                content += '<strong>Opciones:</strong><br>';
                const opciones = ['opcion_1', 'opcion_2', 'opcion_3', 'opcion_4'];
                const letras = ['A', 'B', 'C', 'D'];
                
                opciones.forEach((opcion, index) => {
                    const valor = document.querySelector(`input[name="${opcion}"]`).value;
                    if (valor.trim()) {
                        const esCorrecta = document.querySelector(`input[name="respuesta_correcta"]:checked`)?.value == (index + 1);
                        content += `${letras[index]}) ${valor} ${esCorrecta ? '<span class="badge bg-success">Correcta</span>' : ''}<br>`;
                    }
                });
            } else if (tipo === 'verdadero_falso') {
                const respuestaVF = document.querySelector('input[name="respuesta_vf"]:checked')?.value;
                content += `<strong>Tipo:</strong> Verdadero/Falso<br>`;
                if (respuestaVF) {
                    content += `<strong>Respuesta correcta:</strong> <span class="badge bg-success">${respuestaVF}</span>`;
                }
            } else if (tipo === 'abierta') {
                const respuestaAbierta = document.querySelector('textarea[name="respuesta_abierta"]').value;
                content += `<strong>Tipo:</strong> Respuesta Abierta<br>`;
                if (respuestaAbierta.trim()) {
                    content += `<strong>Respuesta modelo:</strong> ${respuestaAbierta}`;
                }
            }

            previewContent.innerHTML = content;
            preview.style.display = 'block';
        }

        // Limpiar formulario
        function clearForm() {
            if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
                document.getElementById('preguntaForm').reset();
                document.getElementById('preview').style.display = 'none';
                document.getElementById('tipo_pregunta').dispatchEvent(new Event('change'));
            }
        }

        // Validación del formulario
        document.getElementById('preguntaForm').addEventListener('submit', function(e) {
            const enunciado = document.getElementById('enunciado').value.trim();
            const tipo = document.getElementById('tipo_pregunta').value;
            const puntuacion = document.getElementById('puntuacion').value;

            if (!enunciado || !tipo) {
                e.preventDefault();
                alert('Por favor completa todos los campos obligatorios');
                return;
            }

            if (parseFloat(puntuacion) <= 0) {
                e.preventDefault();
                alert('La puntuación debe ser mayor a 0');
                return;
            }

            // Validaciones específicas por tipo
            if (tipo === 'multiple') {
                const opciones = document.querySelectorAll('input[name^="opcion_"]');
                let opcionesCompletas = 0;
                opciones.forEach(opcion => {
                    if (opcion.value.trim()) opcionesCompletas++;
                });

                if (opcionesCompletas < 2) {
                    e.preventDefault();
                    alert('Debes completar al menos 2 opciones para preguntas múltiples');
                    return;
                }

                if (!document.querySelector('input[name="respuesta_correcta"]:checked')) {
                    e.preventDefault();
                    alert('Debes seleccionar cuál es la respuesta correcta');
                    return;
                }
            } else if (tipo === 'verdadero_falso') {
                if (!document.querySelector('input[name="respuesta_vf"]:checked')) {
                    e.preventDefault();
                    alert('Debes seleccionar si la respuesta es Verdadero o Falso');
                    return;
                }
            } else if (tipo === 'abierta') {
                const respuestaAbierta = document.querySelector('textarea[name="respuesta_abierta"]').value.trim();
                if (!respuestaAbierta) {
                    e.preventDefault();
                    alert('Debes escribir una respuesta modelo para preguntas abiertas');
                    return;
                }
            }

            // Mostrar loading
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
            btn.disabled = true;

            // Restaurar después de 10 segundos si hay error
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 10000);
        });

        // Marcar visualmente la opción correcta
        document.addEventListener('change', function(e) {
            if (e.target.name === 'respuesta_correcta') {
                document.querySelectorAll('input[name^="opcion_"]').forEach((input, index) => {
                    if (e.target.value == (index + 1)) {
                        input.classList.add('correct-option');
                    } else {
                        input.classList.remove('correct-option');
                    }
                });
            }
        });
    </script>
</body>
</html>