<?php
require_once 'config.php';
require_once 'ExamGenerator.php';

// Verificar si el usuario está logueado
if (!isLoggedIn()) {
    redirect('login.php');
}

// Verificar que se pasó el ID del examen
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    showMessage('ID de examen inválido', 'error');
    redirect('index.php');
}

$examGenerator = new ExamGenerator();
$examen = $examGenerator->getExamenCompleto($_GET['id'], $_SESSION['user_id']);

if (!$examen) {
    showMessage('Examen no encontrado o no tienes permiso para verlo', 'error');
    redirect('index.php');
}

$message = getMessage();
$user = getCurrentUser();

// Calcular puntuación total
$puntuacion_total = 0;
foreach ($examen['preguntas'] as $pregunta) {
    $puntuacion_total += $pregunta['puntuacion_asignada'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($examen['titulo']); ?> - Generador de Exámenes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .exam-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .question-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        .question-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .option-badge {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        .option-correct {
            background-color: #28a745;
            color: white;
        }
        .option-normal {
            background-color: #e9ecef;
            color: #495057;
        }
        .difficulty-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .print-hide {
            display: block;
        }
        .answer-key {
            display: none;
        }
        .answer-key.show {
            display: block;
        }
        @media print {
            .print-hide {
                display: none !important;
            }
            .question-card {
                break-inside: avoid;
                border: 1px solid #ddd !important;
                margin-bottom: 1rem !important;
                box-shadow: none !important;
            }
            body {
                font-size: 12px;
            }
            .card-body {
                padding: 0.5rem !important;
            }
            .exam-header {
                background: #f8f9fa !important;
                color: #000 !important;
            }
            .answer-key {
                display: none !important;
            }
        }
        .stats-mini {
            font-size: 0.9rem;
        }
        .true-false-options {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .tf-option {
            flex: 1;
            text-align: center;
            padding: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg gradient-bg print-hide">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Volver al Inicio
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>
                        Opciones
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimir Examen
                        </a></li>
                        <li><a class="dropdown-item" href="generar_pdf.php?id=<?php echo $examen['id_examen']; ?>" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i>Generar PDF
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="showAnswers()">
                            <i class="fas fa-eye me-2"></i>Mostrar/Ocultar Respuestas
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteExam()">
                            <i class="fas fa-trash me-2"></i>Eliminar Examen
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Mensaje de alerta -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show print-hide" role="alert">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Encabezado del examen -->
        <div class="exam-header p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">
                        <i class="fas fa-file-alt me-2"></i>
                        <?php echo htmlspecialchars($examen['titulo']); ?>
                    </h1>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-book me-2"></i>
                        <?php echo htmlspecialchars($examen['nombre_materia']); ?>
                    </p>
                    <?php if ($examen['descripcion']): ?>
                        <p class="mt-2 mb-0 opacity-75">
                            <?php echo htmlspecialchars($examen['descripcion']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="stats-mini">
                        <div class="mb-2">
                            <i class="fas fa-list me-1"></i>
                            <strong><?php echo count($examen['preguntas']); ?></strong> preguntas
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-star me-1"></i>
                            <strong><?php echo number_format($puntuacion_total, 1); ?></strong> puntos
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-chart-bar me-1"></i>
                            Nivel: <strong><?php echo ucfirst($examen['nivel_dificultad']); ?></strong>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d/m/Y H:i', strtotime($examen['fecha_generacion'])); ?>
                        </div>
                        <div class="print-hide">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($examen['nombre'] . ' ' . $examen['apellido']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de acciones rápidas -->
        <div class="row mb-4 print-hide">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>
                                    Imprimir
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="window.open('generar_pdf.php?id=<?php echo $examen['id_examen']; ?>', '_blank')">
                                    <i class="fas fa-download me-1"></i>
                                    Descargar PDF
                                </button>
                                <button class="btn btn-outline-info btn-sm" id="toggleAnswers" onclick="showAnswers()">
                                    <i class="fas fa-eye me-1"></i>
                                    Mostrar Respuestas
                                </button>
                            </div>
                            <div>
                                <span class="badge bg-primary">
                                    Total: <?php echo number_format($puntuacion_total, 1); ?> puntos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instrucciones del examen -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Instrucciones
                </h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Lee cuidadosamente cada pregunta antes de responder</li>
                    <li>Para preguntas de opción múltiple, selecciona la respuesta correcta</li>
                    <li>Para preguntas de verdadero/falso, marca V o F según corresponda</li>
                    <li>Para preguntas abiertas, escribe tu respuesta completa</li>
                    <li>Revisa tus respuestas antes de entregar el examen</li>
                </ul>
            </div>
        </div>

        <!-- Preguntas del examen -->
        <div class="questions-container">
            <?php foreach ($examen['preguntas'] as $index => $pregunta): ?>
                <div class="question-card card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span class="badge bg-primary me-2"><?php echo $pregunta['numero_pregunta']; ?></span>
                            Pregunta <?php echo $pregunta['numero_pregunta']; ?>
                        </h5>
                        <div>
                            <span class="badge difficulty-badge bg-<?php 
                                echo $pregunta['nivel_dificultad'] === 'facil' ? 'success' : 
                                    ($pregunta['nivel_dificultad'] === 'medio' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo ucfirst($pregunta['nivel_dificultad']); ?>
                            </span>
                            <span class="badge bg-secondary ms-1">
                                <?php echo $pregunta['puntuacion_asignada']; ?> pts
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Enunciado de la pregunta -->
                        <div class="question-text mb-3">
                            <p class="fs-5 fw-medium mb-0">
                                <?php echo nl2br(htmlspecialchars($pregunta['enunciado'])); ?>
                            </p>
                        </div>

                        <?php if ($pregunta['tipo_pregunta'] === 'multiple'): ?>
                            <!-- Pregunta de opción múltiple -->
                            <div class="options-container">
                                <?php foreach ($pregunta['opciones'] as $opcion): ?>
                                    <div class="option-item d-flex align-items-center mb-2">
                                        <span class="option-badge <?php echo $opcion['es_correcta'] ? 'option-correct answer-key' : 'option-normal'; ?>">
                                            <?php echo $opcion['letra_opcion']; ?>
                                        </span>
                                        <span class="option-text">
                                            <?php echo htmlspecialchars($opcion['texto_opcion']); ?>
                                            <?php if ($opcion['es_correcta']): ?>
                                                <i class="fas fa-check-circle text-success ms-2 answer-key"></i>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($pregunta['tipo_pregunta'] === 'verdadero_falso'): ?>
                            <!-- Pregunta de verdadero/falso -->
                            <div class="true-false-options">
                                <div class="tf-option">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <div><strong>Verdadero</strong></div>
                                </div>
                                <div class="tf-option">
                                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                    <div><strong>Falso</strong></div>
                                </div>
                            </div>
                            <!-- Mostrar respuesta correcta -->
                            <div class="answer-key mt-2">
                                <div class="alert alert-success">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Respuesta correcta:</strong> 
                                    <?php 
                                    // Para preguntas V/F, necesitamos obtener la respuesta del banco
                                    if (isset($pregunta['respuestas']) && !empty($pregunta['respuestas'])) {
                                        echo htmlspecialchars($pregunta['respuestas'][0]['respuesta_correcta']);
                                    } else {
                                        echo "Verdadero"; // Valor por defecto
                                    }
                                    ?>
                                </div>
                            </div>

                        <?php elseif ($pregunta['tipo_pregunta'] === 'abierta'): ?>
                            <!-- Pregunta abierta -->
                            <div class="open-question">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Escribe tu respuesta aquí..." style="height: 100px;" disabled></textarea>
                                    <label>Espacio para la respuesta</label>
                                </div>
                                
                                <!-- Mostrar respuestas correctas -->
                                <?php if (isset($pregunta['respuestas']) && !empty($pregunta['respuestas'])): ?>
                                    <div class="answer-key mt-3">
                                        <div class="alert alert-success">
                                            <i class="fas fa-lightbulb me-2"></i>
                                            <strong>Respuestas correctas:</strong>
                                            <ul class="mb-0 mt-2">
                                                <?php foreach ($pregunta['respuestas'] as $respuesta): ?>
                                                    <li>
                                                        <?php echo htmlspecialchars($respuesta['respuesta_correcta']); ?>
                                                        <?php if ($respuesta['es_exacta']): ?>
                                                            <span class="badge bg-warning ms-1">Exacta</span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Información adicional para impresión -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">Información del Estudiante</h6>
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-6 text-start">
                                    <strong>Nombre:</strong>
                                    <div class="border-bottom border-dark mt-1" style="height: 20px;"></div>
                                </div>
                                <div class="col-6 text-start">
                                    <strong>Fecha:</strong>
                                    <div class="border-bottom border-dark mt-1" style="height: 20px;"></div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 text-start">
                                    <strong>Firma:</strong>
                                    <div class="border-bottom border-dark mt-1" style="height: 20px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">Calificación</h6>
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Puntos obtenidos:</strong>
                                    <div class="fs-3 fw-bold text-primary mt-2">_____ / <?php echo number_format($puntuacion_total, 1); ?></div>
                                </div>
                                <div class="col-6">
                                    <strong>Calificación:</strong>
                                    <div class="fs-3 fw-bold text-success mt-2">_____%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let answersVisible = false;

        function showAnswers() {
            const answerElements = document.querySelectorAll('.answer-key');
            const toggleBtn = document.getElementById('toggleAnswers');
            
            answersVisible = !answersVisible;
            
            answerElements.forEach(element => {
                if (answersVisible) {
                    element.classList.add('show');
                } else {
                    element.classList.remove('show');
                }
            });
            
            if (answersVisible) {
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Ocultar Respuestas';
                toggleBtn.classList.remove('btn-outline-info');
                toggleBtn.classList.add('btn-info');
            } else {
                toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Mostrar Respuestas';
                toggleBtn.classList.remove('btn-info');
                toggleBtn.classList.add('btn-outline-info');
            }
        }

        function deleteExam() {
            if (confirm('¿Estás seguro de que quieres eliminar este examen? Esta acción no se puede deshacer.')) {
                // Aquí iría la lógica para eliminar el examen
                window.location.href = 'eliminar_examen.php?id=<?php echo $examen['id_examen']; ?>';
            }
        }

        // Optimizar para impresión
        window.addEventListener('beforeprint', function() {
            // Ocultar respuestas al imprimir
            const answerElements = document.querySelectorAll('.answer-key');
            answerElements.forEach(element => {
                element.style.display = 'none';
            });
        });

        window.addEventListener('afterprint', function() {
            // Restaurar estado de respuestas después de imprimir
            if (answersVisible) {
                const answerElements = document.querySelectorAll('.answer-key');
                answerElements.forEach(element => {
                    element.style.display = 'block';
                });
            }
        });

        // Confirmar antes de salir si hay cambios
        window.addEventListener('beforeunload', function(e) {
            // Solo si el usuario ha interactuado con el examen
            const textareas = document.querySelectorAll('textarea');
            let hasContent = false;
            textareas.forEach(textarea => {
                if (textarea.value.trim() !== '') {
                    hasContent = true;
                }
            });
            
            if (hasContent) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>