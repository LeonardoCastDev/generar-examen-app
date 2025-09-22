<?php
require_once 'config.php';
require_once 'ExamGenerator.php';

// Requerir autenticación
requireAuth();

$examGenerator = new ExamGenerator();
$user = getCurrentUser();

if (!$user) {
    showMessage('Error al cargar datos del usuario', 'error');
    redirect('login.php');
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_pregunta'])) {
        $id_pregunta = intval($_POST['id_pregunta']);
        $result = $examGenerator->eliminarPregunta($id_pregunta, $_SESSION['user_id']);
        showMessage($result['message'], $result['success'] ? 'success' : 'error');
    } 
    elseif (isset($_POST['restaurar_pregunta'])) {
        $id_pregunta = intval($_POST['id_pregunta']);
        $result = $examGenerator->restaurarPregunta($id_pregunta, $_SESSION['user_id']);
        showMessage($result['message'], $result['success'] ? 'success' : 'error');
    }
    
    // Redirigir para evitar reenvío del formulario
    redirect('gestionar_preguntas.php');
}

// Obtener filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_dificultad = isset($_GET['dificultad']) ? $_GET['dificultad'] : '';
$mostrar_eliminadas = isset($_GET['eliminadas']) && $_GET['eliminadas'] == '1';
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Obtener materias y preguntas
$materias = $examGenerator->getMaterias();
$datos = $examGenerator->getPreguntasBanco(1, $filtro_tipo, $filtro_dificultad, $pagina, 10); // ID 1 = ADMISION
$estadisticas = $examGenerator->getEstadisticasBanco(1);

// Si se solicitan preguntas eliminadas, cambiar la consulta
if ($mostrar_eliminadas) {
    // Consulta personalizada para preguntas eliminadas
    try {
        $sql = "
            SELECT bp.*, m.nombre_materia
            FROM banco_preguntas bp
            JOIN materias m ON bp.id_materia = m.id_materia
            WHERE bp.activa = 0 AND bp.id_materia = 1
            ORDER BY bp.fecha_creacion DESC
        ";
        
        global $pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $preguntas_eliminadas = $stmt->fetchAll();
        
        $datos = [
            'preguntas' => $preguntas_eliminadas,
            'total' => count($preguntas_eliminadas),
            'pagina_actual' => 1,
            'total_paginas' => 1,
            'limite' => 999
        ];
    } catch (Exception $e) {
        error_log("Error obteniendo preguntas eliminadas: " . $e->getMessage());
        $datos = ['preguntas' => [], 'total' => 0, 'pagina_actual' => 1, 'total_paginas' => 0, 'limite' => 10];
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Preguntas - Generador de Exámenes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .pregunta-card {
            border-left: 4px solid #667eea;
        }
        .pregunta-eliminada {
            border-left-color: #dc3545;
            background-color: #f8f9fa;
            opacity: 0.8;
        }
        .stats-card {
            background: linear-gradient(45deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .filter-card {
            background: linear-gradient(45deg, #ffc107 0%, #fd7e14 100%);
            color: white;
        }
        .btn-danger:hover {
            transform: scale(1.05);
        }
        .pregunta-preview {
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pagination .page-link {
            color: #667eea;
        }
        .pagination .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
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
                        <li><a class="dropdown-item" href="agregar_pregunta.php"><i class="fas fa-plus me-2"></i>Agregar Pregunta</a></li>
                        <li><a class="dropdown-item" href="mis_examenes.php"><i class="fas fa-file-alt me-2"></i>Mis Exámenes</a></li>
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
                        <i class="fas fa-cogs text-primary me-3"></i>
                        Gestionar Banco de Preguntas
                    </h1>
                    <p class="lead text-muted">Administra las preguntas disponibles para ADMISION</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Estadísticas -->
            <div class="col-lg-4 mb-4">
                <div class="card stats-card card-hover">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <i class="fas fa-chart-bar me-2"></i>
                            Estadísticas del Banco
                        </h5>
                        <div class="row">
                            <div class="col-6">
                                <h3><?php echo $estadisticas['total_activas'] ?? 0; ?></h3>
                                <small>Preguntas Activas</small>
                            </div>
                            <div class="col-6">
                                <h3><?php echo $estadisticas['total_eliminadas'] ?? 0; ?></h3>
                                <small>Eliminadas</small>
                            </div>
                        </div>
                        <hr class="border-white">
                        <div class="row mt-3">
                            <?php if (!empty($estadisticas['por_tipo'])): ?>
                                <?php foreach ($estadisticas['por_tipo'] as $tipo): ?>
                                    <div class="col-4 mb-2">
                                        <small>
                                            <?php 
                                            $tipo_nombre = [
                                                'multiple' => 'Múltiple',
                                                'verdadero_falso' => 'V/F',
                                                'abierta' => 'Abierta'
                                            ];
                                            echo $tipo_nombre[$tipo['tipo_pregunta']] ?? $tipo['tipo_pregunta'];
                                            ?>
                                            <br><strong><?php echo $tipo['cantidad']; ?></strong>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="col-lg-8 mb-4">
                <div class="card filter-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-filter me-2"></i>
                            Filtros y Opciones
                        </h5>
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Tipo de Pregunta</label>
                                <select name="tipo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="multiple" <?php echo ($filtro_tipo == 'multiple') ? 'selected' : ''; ?>>Opción Múltiple</option>
                                    <option value="verdadero_falso" <?php echo ($filtro_tipo == 'verdadero_falso') ? 'selected' : ''; ?>>Verdadero/Falso</option>
                                    <option value="abierta" <?php echo ($filtro_tipo == 'abierta') ? 'selected' : ''; ?>>Respuesta Abierta</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Dificultad</label>
                                <select name="dificultad" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="facil" <?php echo ($filtro_dificultad == 'facil') ? 'selected' : ''; ?>>Fácil</option>
                                    <option value="medio" <?php echo ($filtro_dificultad == 'medio') ? 'selected' : ''; ?>>Medio</option>
                                    <option value="dificil" <?php echo ($filtro_dificultad == 'dificil') ? 'selected' : ''; ?>>Difícil</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="eliminadas" value="1" 
                                           <?php echo $mostrar_eliminadas ? 'checked' : ''; ?> id="mostrar_eliminadas">
                                    <label class="form-check-label" for="mostrar_eliminadas">
                                        <i class="fas fa-trash me-1"></i>
                                        Mostrar eliminadas
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search me-1"></i>
                                    Filtrar
                                </button>
                                <a href="gestionar_preguntas.php" class="btn btn-outline-light">
                                    <i class="fas fa-times me-1"></i>
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>
                            <?php if ($mostrar_eliminadas): ?>
                                <i class="fas fa-trash text-danger me-2"></i>
                                Preguntas Eliminadas (<?php echo $datos['total']; ?>)
                            <?php else: ?>
                                <i class="fas fa-list text-primary me-2"></i>
                                Preguntas Activas (<?php echo $datos['total']; ?>)
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div>
                        <a href="agregar_pregunta.php" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Pregunta
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de preguntas -->
        <div class="row">
            <?php if (empty($datos['preguntas'])): ?>
                <div class="col-12">
                    <div class="card text-center py-5">
                        <div class="card-body">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No se encontraron preguntas</h5>
                            <p class="text-muted">
                                <?php if ($mostrar_eliminadas): ?>
                                    No hay preguntas eliminadas que mostrar.
                                <?php else: ?>
                                    Prueba cambiando los filtros o agrega nuevas preguntas.
                                <?php endif; ?>
                            </p>
                            <?php if (!$mostrar_eliminadas): ?>
                                <a href="agregar_pregunta.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Agregar Primera Pregunta
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($datos['preguntas'] as $pregunta): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card pregunta-card card-hover <?php echo !$mostrar_eliminadas ? '' : 'pregunta-eliminada'; ?>">
                            <div class="card-header d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-question-circle me-1"></i>
                                        Pregunta #<?php echo $pregunta['id_pregunta_banco']; ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?php 
                                        $tipo_nombres = [
                                            'multiple' => 'Opción Múltiple',
                                            'verdadero_falso' => 'Verdadero/Falso',
                                            'abierta' => 'Respuesta Abierta'
                                        ];
                                        echo $tipo_nombres[$pregunta['tipo_pregunta']] ?? $pregunta['tipo_pregunta'];
                                        ?>
                                        • <?php echo ucfirst($pregunta['nivel_dificultad']); ?>
                                        • <?php echo $pregunta['puntuacion']; ?> pts
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" onclick="verPregunta(<?php echo $pregunta['id_pregunta_banco']; ?>)">
                                                <i class="fas fa-eye me-2"></i>Ver Completa
                                            </button>
                                        </li>
                                        <?php if ($mostrar_eliminadas): ?>
                                            <li>
                                                <button class="dropdown-item text-success" onclick="restaurarPregunta(<?php echo $pregunta['id_pregunta_banco']; ?>)">
                                                    <i class="fas fa-undo me-2"></i>Restaurar
                                                </button>
                                            </li>
                                        <?php else: ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" onclick="eliminarPregunta(<?php echo $pregunta['id_pregunta_banco']; ?>, '<?php echo htmlspecialchars(substr($pregunta['enunciado'], 0, 50)); ?>...')">
                                                    <i class="fas fa-trash me-2"></i>Eliminar
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="pregunta-preview">
                                    <p class="card-text"><?php echo htmlspecialchars($pregunta['enunciado']); ?></p>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($pregunta['fecha_creacion'])); ?>
                                    </small>
                                    <?php if ($mostrar_eliminadas): ?>
                                        <span class="badge bg-danger">Eliminada</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <?php if ($datos['total_paginas'] > 1): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <nav aria-label="Navegación de preguntas">
                        <ul class="pagination justify-content-center">
                            <!-- Página anterior -->
                            <li class="page-item <?php echo ($datos['pagina_actual'] <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $datos['pagina_actual'] - 1; ?>&tipo=<?php echo $filtro_tipo; ?>&dificultad=<?php echo $filtro_dificultad; ?>&eliminadas=<?php echo $mostrar_eliminadas ? '1' : '0'; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Páginas -->
                            <?php
                            $inicio = max(1, $datos['pagina_actual'] - 2);
                            $fin = min($datos['total_paginas'], $datos['pagina_actual'] + 2);
                            
                            for ($i = $inicio; $i <= $fin; $i++):
                            ?>
                                <li class="page-item <?php echo ($i == $datos['pagina_actual']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?>&tipo=<?php echo $filtro_tipo; ?>&dificultad=<?php echo $filtro_dificultad; ?>&eliminadas=<?php echo $mostrar_eliminadas ? '1' : '0'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Página siguiente -->
                            <li class="page-item <?php echo ($datos['pagina_actual'] >= $datos['total_paginas']) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $datos['pagina_actual'] + 1; ?>&tipo=<?php echo $filtro_tipo; ?>&dificultad=<?php echo $filtro_dificultad; ?>&eliminadas=<?php echo $mostrar_eliminadas ? '1' : '0'; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="text-center text-muted">
                        Mostrando <?php echo count($datos['preguntas']); ?> de <?php echo $datos['total']; ?> preguntas
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal para ver pregunta completa -->
    <div class="modal fade" id="preguntaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        Vista Completa de la Pregunta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="preguntaModalBody">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formularios ocultos para acciones -->
    <form id="eliminarForm" method="POST" style="display: none;">
        <input type="hidden" name="eliminar_pregunta" value="1">
        <input type="hidden" name="id_pregunta" id="eliminar_id">
    </form>

    <form id="restaurarForm" method="POST" style="display: none;">
        <input type="hidden" name="restaurar_pregunta" value="1">
        <input type="hidden" name="id_pregunta" id="restaurar_id">
    </form>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ver pregunta completa
        function verPregunta(idPregunta) {
            const modal = new bootstrap.Modal(document.getElementById('preguntaModal'));
            const modalBody = document.getElementById('preguntaModalBody');
            
            modalBody.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando pregunta...</p>
                </div>
            `;
            
            modal.show();
            
            // Simular carga de pregunta (aquí podrías hacer una llamada AJAX)
            setTimeout(() => {
                modalBody.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Esta es una vista simplificada. En una implementación completa, 
                        aquí se cargarían todos los detalles de la pregunta con ID ${idPregunta}.
                    </div>
                    <p>Para ver la pregunta completa, necesitarías implementar un endpoint AJAX 
                    que use el método <code>getPreguntaCompleta()</code> de ExamGenerator.</p>
                `;
            }, 1000);
        }

        // Eliminar pregunta
        function eliminarPregunta(idPregunta, enunciado) {
            if (confirm(`¿Estás seguro de que deseas eliminar esta pregunta?\n\n"${enunciado}"\n\nEsta acción se puede deshacer.`)) {
                document.getElementById('eliminar_id').value = idPregunta;
                document.getElementById('eliminarForm').submit();
            }
        }

        // Restaurar pregunta
        function restaurarPregunta(idPregunta) {
            if (confirm('¿Estás seguro de que deseas restaurar esta pregunta?')) {
                document.getElementById('restaurar_id').value = idPregunta;
                document.getElementById('restaurarForm').submit();
            }
        }

        // Auto-submit del formulario de filtros cuando se cambia el checkbox
        document.getElementById('mostrar_eliminadas').addEventListener('change', function() {
            this.closest('form').submit();
        });

        // Confirmación adicional para eliminar múltiples elementos (funcionalidad futura)
        function confirmarAccionMasiva(accion, cantidad) {
            const mensaje = accion === 'eliminar' 
                ? `¿Eliminar ${cantidad} preguntas seleccionadas?`
                : `¿Restaurar ${cantidad} preguntas seleccionadas?`;
            
            return confirm(mensaje);
        }

        // Función para mostrar tooltips en elementos truncados
        document.querySelectorAll('.pregunta-preview p').forEach(function(element) {
            if (element.scrollHeight > element.clientHeight) {
                element.setAttribute('title', element.textContent);
                element.style.cursor = 'help';
            }
        });
    </script>
</body>
</html>