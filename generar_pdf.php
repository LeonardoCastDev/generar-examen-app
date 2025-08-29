<?php
require_once 'config.php';
require_once 'ExamGenerator.php';

// Verificar autenticación
if (!isLoggedIn()) {
    showMessage('Debes iniciar sesión para acceder a esta página', 'error');
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

// Calcular puntuación total
$puntuacion_total = 0;
foreach ($examen['preguntas'] as $pregunta) {
    $puntuacion_total += $pregunta['puntuacion_asignada'];
}

// Configurar headers para PDF
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($examen['titulo']); ?> - PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.4;
            color: #333;
        }
        .exam-header {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #dee2e6;
        }
        .exam-title {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 24px;
        }
        .exam-info {
            margin: 5px 0;
            color: #6c757d;
        }
        .question-block {
            margin-bottom: 25px;
            page-break-inside: avoid;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
        }
        .question-header {
            background: #e9ecef;
            padding: 8px 12px;
            margin: -15px -15px 15px -15px;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
        }
        .question-text {
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .option {
            margin: 8px 0;
            padding: 5px 0;
        }
        .option-letter {
            display: inline-block;
            width: 25px;
            height: 25px;
            border: 2px solid #6c757d;
            border-radius: 50%;
            text-align: center;
            line-height: 21px;
            font-weight: bold;
            margin-right: 10px;
        }
        .tf-options {
            display: flex;
            gap: 30px;
            margin-top: 10px;
        }
        .tf-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
        }
        .answer-space {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            min-height: 80px;
            margin-top: 10px;
            background: #f8f9fa;
        }
        .student-info {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 30px 0;
            background: #f8f9fa;
        }
        .info-row {
            display: flex;
            margin-bottom: 15px;
        }
        .info-field {
            flex: 1;
            margin-right: 20px;
        }
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-line {
            border-bottom: 1px solid #333;
            height: 25px;
            width: 100%;
        }
        .grade-section {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .points-display {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .difficulty-badge {
            background: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .difficulty-easy { background: #28a745; }
        .difficulty-medio { background: #ffc107; color: #000; }
        .difficulty-dificil { background: #dc3545; }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body { margin: 0; }
            .question-block { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Encabezado del examen -->
    <div class="exam-header">
        <h1 class="exam-title"><?php echo htmlspecialchars($examen['titulo']); ?></h1>
        <div class="exam-info">
            <strong>Materia:</strong> <?php echo htmlspecialchars($examen['nombre_materia']); ?>
        </div>
        <?php if ($examen['descripcion']): ?>
            <div class="exam-info">
                <strong>Descripción:</strong> <?php echo htmlspecialchars($examen['descripcion']); ?>
            </div>
        <?php endif; ?>
        <div class="exam-info">
            <strong>Total de preguntas:</strong> <?php echo count($examen['preguntas']); ?> |
            <strong>Puntuación total:</strong> <?php echo number_format($puntuacion_total, 1); ?> puntos |
            <strong>Nivel:</strong> <?php echo ucfirst($examen['nivel_dificultad']); ?>
        </div>
        <div class="exam-info">
            <strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i', strtotime($examen['fecha_generacion'])); ?>
        </div>
    </div>

    <!-- Información del estudiante -->
    <div class="student-info">
        <h3 style="margin-top: 0;">Información del Estudiante</h3>
        <div class="info-row">
            <div class="info-field">
                <div class="info-label">Nombre completo:</div>
                <div class="info-line"></div>
            </div>
            <div class="info-field">
                <div class="info-label">Fecha:</div>
                <div class="info-line"></div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-field">
                <div class="info-label">Grupo/Sección:</div>
                <div class="info-line"></div>
            </div>
            <div class="info-field">
                <div class="info-label">Firma:</div>
                <div class="info-line"></div>
            </div>
        </div>
    </div>

    <!-- Instrucciones -->
    <div style="background: #e3f2fd; padding: 15px; border-radius: 6px; margin-bottom: 25px;">
        <h3 style="margin-top: 0; color: #1976d2;">Instrucciones:</h3>
        <ul style="margin-bottom: 0;">
            <li>Lee cuidadosamente cada pregunta antes de responder</li>
            <li>Para preguntas de opción múltiple, encierra en un círculo la letra de la respuesta correcta</li>
            <li>Para preguntas de verdadero/falso, marca claramente V o F</li>
            <li>Para preguntas abiertas, escribe tu respuesta completa en el espacio proporcionado</li>
            <li>Revisa todas tus respuestas antes de entregar el examen</li>
        </ul>
    </div>

    <!-- Preguntas -->
    <?php foreach ($examen['preguntas'] as $index => $pregunta): ?>
        <div class="question-block">
            <div class="question-header">
                Pregunta <?php echo $pregunta['numero_pregunta']; ?>
                <span class="difficulty-badge difficulty-<?php echo $pregunta['nivel_dificultad']; ?>">
                    <?php echo ucfirst($pregunta['nivel_dificultad']); ?>
                </span>
                <span style="float: right;"><?php echo $pregunta['puntuacion_asignada']; ?> punto(s)</span>
            </div>
            
            <div class="question-text">
                <?php echo nl2br(htmlspecialchars($pregunta['enunciado'])); ?>
            </div>

            <?php if ($pregunta['tipo_pregunta'] === 'multiple'): ?>
                <!-- Opciones múltiples -->
                <?php foreach ($pregunta['opciones'] as $opcion): ?>
                    <div class="option">
                        <span class="option-letter"><?php echo $opcion['letra_opcion']; ?></span>
                        <?php echo htmlspecialchars($opcion['texto_opcion']); ?>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($pregunta['tipo_pregunta'] === 'verdadero_falso'): ?>
                <!-- Verdadero/Falso -->
                <div class="tf-options">
                    <div class="tf-option">
                        <strong>( ) Verdadero</strong>
                    </div>
                    <div class="tf-option">
                        <strong>( ) Falso</strong>
                    </div>
                </div>

            <?php elseif ($pregunta['tipo_pregunta'] === 'abierta'): ?>
                <!-- Pregunta abierta -->
                <div style="margin-top: 15px;">
                    <strong>Respuesta:</strong>
                    <div class="answer-space"></div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <!-- Sección de calificación -->
    <div class="grade-section">
        <h3>Calificación</h3>
        <div style="display: flex; justify-content: space-around; align-items: center;">
            <div>
                <div><strong>Puntos obtenidos:</strong></div>
                <div class="points-display">_____ / <?php echo number_format($puntuacion_total, 1); ?></div>
            </div>
            <div>
                <div><strong>Porcentaje:</strong></div>
                <div class="points-display">_____%</div>
            </div>
            <div>
                <div><strong>Calificación final:</strong></div>
                <div class="points-display">_____</div>
            </div>
        </div>
        <div style="margin-top: 20px; text-align: left;">
            <strong>Observaciones del profesor:</strong>
            <div style="border-bottom: 1px solid #333; height: 25px; margin: 10px 0;"></div>
            <div style="border-bottom: 1px solid #333; height: 25px; margin: 10px 0;"></div>
        </div>
    </div>

    <!-- Footer con información adicional -->
    <div style="margin-top: 30px; text-align: center; color: #6c757d; font-size: 12px;">
        <p>Examen generado el <?php echo date('d/m/Y H:i'); ?> por el Sistema Generador de Exámenes</p>
        <p>Profesor: <?php echo htmlspecialchars($examen['nombre'] . ' ' . $examen['apellido']); ?></p>
    </div>

    <!-- Script para auto-imprimir -->
    <script>
        // Auto-abrir diálogo de impresión al cargar la página
        window.onload = function() {
            window.print();
        };

        // Cerrar ventana después de imprimir o cancelar
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>