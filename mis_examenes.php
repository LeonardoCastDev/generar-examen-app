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
    if (isset($_POST['eliminar_examen'])) {
        $id_examen = intval($_POST['id_examen']);
        $result = $examGenerator->eliminarExamen($id_examen, $_SESSION['user_id']);
        showMessage($result['message'], $result['success'] ? 'success' : 'error');
        redirect('mis_examenes.php');
    }
}

// Filtros
$filtro_materia = isset($_GET['materia']) ? intval($_GET['materia']) : 0;
$filtro_dificultad = isset($_GET['dificultad']) ? $_GET['dificultad'] : '';
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_desc';

// Obtener exámenes con filtros
$examenes = $examGenerator->getExamenesUsuario($_SESSION['user_id']);
$materias = $examGenerator->getMaterias();

// Aplicar filtros manualmente (mejorar ExamGenerator después)
if ($filtro_materia > 0) {
    $examenes = array_filter($examenes, function($examen) use ($filtro_materia) {
        return $examen['id_materia'] == $filtro_materia;
    });
}

if ($filtro_dificultad) {
    $examenes = array_filter($examenes, function($examen) use ($filtro_dificultad) {
        return $examen['nivel_dificultad'] == $filtro_dificultad;
    });
}

if ($busqueda) {
    $examenes = array_filter($examenes, function($examen) use ($busqueda) {
        return stripos($examen['titulo'], $busqueda) !== false || 
               stripos($examen['descripcion'], $busqueda) !== false ||
               stripos($examen['nombre_materia'], $busqueda) !== false;
    });
}

// Ordenar
switch ($orden) {
    case 'titulo_asc':
        usort($examenes, function($a, $b) {
            return strcasecmp($a['titulo'], $b['titulo']);
        });
        break;
    case 'titulo_desc':
        usort($examenes, function($a, $b) {
            return strcasecmp($b['titulo'], $a['titulo']);
        });
        break;
    case 'materia_asc':
        usort($examenes, function($a, $b) {
            return strcasecmp($a['nombre_materia'], $b['nombre_materia']);
        });
        break;
    case 'fecha_asc':
        usort($examenes, function($a, $b) {
            return strtotime($a['fecha_generacion']) - strtotime($b['fecha_generacion']);
        });
        break;
    case 'fecha_desc':
    default:
        usort($examenes, function($a, $b) {
            return strtotime($b['fecha_generacion']) - strtotime($a['fecha_generacion']);
        });
        break;
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Exámenes - Generador de Exámenes</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .exam-card {
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .exam-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .filter-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .stats-mini {
            font-size: 0.9rem;
        }
        .difficulty-badge {
            font-size: 0.75rem;
        }
        .exam-actions {
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        .exam-card:hover .exam-actions {
            opacity: 1;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        .search-highlight {
            background-color: yellow;
            padding: 1px 3px;
            border-radius: 2px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg gradient-bg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Generador de Exámenes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i>Inicio</a></li>
                        <li><a class="dropdown-item" href="agregar_pregunta.php"><i class="fas fa-plus me-2"></i>Agregar Pregunta</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth.php?action=logout"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
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
            <div class="col-md-8">
                <h1 class="display-5">
                    <i class="fas fa-file-alt text-primary me-3"></i>
                    Mis Exámenes
                </h1>
                <p class="lead text-muted">Gestiona y organiza todos tus exámenes generados</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>
                    Crear Nuevo Examen
                </a>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="card mb-4 filter-card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-search me-1"></i>
                            Buscar
                        </label>
                        <input type="text" class="form-control" name="buscar" 
                               value="<?php echo htmlspecialchars($busqueda); ?>" 
                               placeholder="Título, descripción...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-book me-1"></i>
                            Materia
                        </label>
                        <select class="form-select" name="materia">
                            <option value="">Todas las materias</option>
                            <?php foreach ($materias as $materia): ?>
                                <option value="<?php echo $materia['id_materia']; ?>" 
                                        <?php echo $filtro_materia == $materia['id_materia'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($materia['nombre_materia']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-chart-bar me-1"></i>
                            Dificultad
                        </label>
                        <select class="form-select" name="dificultad">
                            <option value="">Todas</option>
                            <option value="facil" <?php echo $filtro_dificultad == 'facil' ? 'selected' : ''; ?>>Fácil</option>
                            <option value="medio" <?php echo $filtro_dificultad == 'medio' ? 'selected' : ''; ?>>Medio</option>
                            <option value="dificil" <?php echo $filtro_dificultad == 'dificil' ? 'selected' : ''; ?>>Difícil</option>
                            <option value="mixto" <?php echo $filtro_dificultad == 'mixto' ? 'selected' : ''; ?>>Mixto</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-sort me-1"></i>
                            Ordenar por
                        </label>
                        <select class="form-select" name="orden">
                            <option value="fecha_desc" <?php echo $orden == 'fecha_desc' ? 'selected' : ''; ?>>Más reciente</option>
                            <option value="fecha_asc" <?php echo $orden == 'fecha_asc' ? 'selected' : ''; ?>>Más antiguo</option>
                            <option value="titulo_asc" <?php echo $orden == 'titulo_asc' ? 'selected' : ''; ?>>Título A-Z</option>
                            <option value="titulo_desc" <?php echo $orden == 'titulo_desc' ? 'selected' : ''; ?>>Título Z-A</option>
                            <option value="materia_asc" <?php echo $orden == 'materia_asc' ? 'selected' : ''; ?>>Materia A-Z</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-light me-2">
                            <i class="fas fa-filter me-1"></i>
                            Filtrar
                        </button>
                        <a href="mis_examenes.php" class="btn btn-outline-light">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                        <h4 class="card-title"><?php echo count($examenes); ?></h4>
                        <p class="card-text text-muted">Total de Exámenes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book fa-2x text-success mb-2"></i>
                        <h4 class="card-title">
                            <?php 
                            $materias_usadas = array_unique(array_column($examenes, 'id_materia'));
                            echo count($materias_usadas);
                            ?>
                        </h4>
                        <p class="card-text text-muted">Materias Cubiertas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-list fa-2x text-info mb-2"></i>
                        <h4 class="card-title">
                            <?php echo array_sum(array_column($examenes, 'total_preguntas')); ?>
                        </h4>
                        <p class="card-text text-muted">Total Preguntas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar fa-2x text-warning mb-2"></i>
                        <h4 class="card-title">
                            <?php 
                            $this_month = date('Y-m');
                            $this_month_count = count(array_filter($examenes, function($e) use ($this_month) {
                                return date('Y-m', strtotime($e['fecha_generacion'])) == $this_month;
                            }));
                            echo $this_month_count;
                            ?>
                        </h4>
                        <p class="card-text text-muted">Este Mes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de exámenes -->
        <?php if (empty($examenes)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox fa-4x mb-3"></i>
                <h3>No se encontraron exámenes</h3>
                <?php if ($busqueda || $filtro_materia || $filtro_dificultad): ?>
                    <p>Intenta cambiar los filtros de búsqueda o 
                       <a href="mis_examenes.php" class="text-decoration-none">ver todos los exámenes</a>
                    </p>
                <?php else: ?>
                    <p>¡Aún no has generado ningún examen!</p>
                    <a href="index.php" class="btn btn-primary btn-lg mt-3">
                        <i class="fas fa-plus me-2"></i>
                        Crear mi Primer Examen
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($examenes as $examen): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card exam-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-truncate me-2">
                                    <a href="ver_examen.php?id=<?php echo $examen['id_examen']; ?>" 
                                       class="text-decoration-none fw-bold">
                                        <?php echo htmlspecialchars($examen['titulo']); ?>
                                    </a>
                                </h6>
                                <div class="exam-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="ver_examen.php?id=<?php echo $examen['id_examen']; ?>">
                                                    <i class="fas fa-eye me-2"></i>Ver Examen
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="generar_pdf.php?id=<?php echo $examen['id_examen']; ?>" target="_blank">
                                                    <i class="fas fa-file-pdf me-2"></i>Generar PDF
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   onclick="eliminarExamen(<?php echo $examen['id_examen']; ?>, '<?php echo htmlspecialchars($examen['titulo'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-trash me-2"></i>Eliminar
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-book me-1"></i>
                                        <?php echo htmlspecialchars($examen['nombre_materia']); ?>
                                    </span>
                                    <span class="badge difficulty-badge bg-<?php 
                                        echo $examen['nivel_dificultad'] === 'facil' ? 'success' : 
                                            ($examen['nivel_dificultad'] === 'medio' ? 'warning' : 
                                             ($examen['nivel_dificultad'] === 'dificil' ? 'danger' : 'info')); 
                                    ?>">
                                        <?php echo ucfirst($examen['nivel_dificultad']); ?>
                                    </span>
                                </div>

                                <?php if ($examen['descripcion']): ?>
                                    <p class="card-text text-muted small mb-2">
                                        <?php echo htmlspecialchars(substr($examen['descripcion'], 0, 100)); ?>
                                        <?php echo strlen($examen['descripcion']) > 100 ? '...' : ''; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="stats-mini mb-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <i class="fas fa-list text-primary"></i>
                                            <div class="fw-bold"><?php echo $examen['total_preguntas']; ?></div>
                                            <small class="text-muted">Preguntas</small>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-calendar text-success"></i>
                                            <div class="fw-bold"><?php echo date('d/m', strtotime($examen['fecha_generacion'])); ?></div>
                                            <small class="text-muted">Creado</small>
                                        </div>
                                        <div class="col-4">
                                            <i class="fas fa-clock text-info"></i>
                                            <div class="fw-bold"><?php echo date('H:i', strtotime($examen['fecha_generacion'])); ?></div>
                                            <small class="text-muted">Hora</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="ver_examen.php?id=<?php echo $examen['id_examen']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        Ver Examen Completo
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <small>
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Creado el <?php echo date('d/m/Y H:i', strtotime($examen['fecha_generacion'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar el examen <strong id="examTitle"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="id_examen" id="examIdToDelete">
                        <button type="submit" name="eliminar_examen" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>
                            Eliminar Definitivamente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function eliminarExamen(id, titulo) {
            document.getElementById('examIdToDelete').value = id;
            document.getElementById('examTitle').textContent = titulo;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Auto-submit del formulario al cambiar filtros
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('select[name="materia"], select[name="dificultad"], select[name="orden"]');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });

        // Resaltar términos de búsqueda
        <?php if ($busqueda): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const searchTerm = <?php echo json_encode($busqueda); ?>;
            if (searchTerm) {
                const regex = new RegExp('(' + searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                document.querySelectorAll('.card-title, .card-text').forEach(element => {
                    if (element.innerHTML.match(regex)) {
                        element.innerHTML = element.innerHTML.replace(regex, '<span class="search-highlight">$1</span>');
                    }
                });
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>