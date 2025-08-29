<?php
require_once 'config.php';
require_once 'ExamGenerator.php';

// Requerir autenticación - esto redirigirá al login si no está autenticado
requireAuth();

$examGenerator = new ExamGenerator();
$materias = $examGenerator->getMaterias();
$user = getCurrentUser();

// Si no se puede obtener el usuario actual, algo está mal con la sesión
if (!$user) {
    showMessage('Error al cargar datos del usuario', 'error');
    redirect('login.php');
}

// Procesar generación de examen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_examen'])) {
    // Validar datos recibidos
    $id_materia = isset($_POST['id_materia']) ? intval($_POST['id_materia']) : 0;
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $total_preguntas = isset($_POST['total_preguntas']) ? intval($_POST['total_preguntas']) : 10;
    $dificultad = isset($_POST['dificultad']) ? $_POST['dificultad'] : 'mixto';
    
    // Validaciones básicas
    if (empty($titulo)) {
        showMessage('El título del examen es obligatorio', 'error');
    } elseif ($id_materia <= 0) {
        showMessage('Debes seleccionar una materia válida', 'error');
    } elseif ($total_preguntas <= 0 || $total_preguntas > 50) {
        showMessage('El número de preguntas debe estar entre 1 y 50', 'error');
    } else {
        // Intentar generar el examen
        $result = $examGenerator->generarExamen(
            $id_materia,
            $titulo,
            $descripcion,
            $total_preguntas,
            $dificultad,
            $_SESSION['user_id']
        );
        
        showMessage($result['message'], $result['success'] ? 'success' : 'error');
        
        if ($result['success']) {
            redirect('ver_examen.php?id=' . $result['id_examen']);
        }
    }
}

// Obtener mensaje y examenes recientes
$message = getMessage();
$examenes_recientes = $examGenerator->getExamenesUsuario($_SESSION['user_id'], 5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador Automático de Exámenes</title>
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
        .exam-card {
            border-left: 4px solid #667eea;
        }
        .stats-card {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .loading {
            display: none;
        }
        .btn-loading {
            position: relative;
            overflow: hidden;
        }
        .btn-loading:disabled {
            opacity: 0.8;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg gradient-bg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Generador de Exámenes
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($user['nombre']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="mis_examenes.php"><i class="fas fa-file-alt me-2"></i>Mis Exámenes</a></li>
                        <li><a class="dropdown-item" href="agregar_pregunta.php"><i class="fas fa-plus me-2"></i>Agregar Pregunta</a></li>
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
                        <i class="fas fa-magic text-primary me-3"></i>
                        Generador de Exámenes
                    </h1>
                    <p class="lead text-muted">Crea exámenes personalizados de forma rápida y sencilla</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Formulario de generación -->
            <div class="col-lg-8">
                <div class="card shadow card-hover">
                    <div class="card-header gradient-bg">
                        <h4 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Generar Nuevo Examen
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="examForm" onsubmit="return validateForm()">
                            <input type="hidden" name="generar_examen" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titulo" class="form-label">
                                        <i class="fas fa-heading me-1"></i>
                                        Título del Examen <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required 
                                           placeholder="Ej: Examen de Matemáticas - Unidad 1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_materia" class="form-label">
                                        <i class="fas fa-book me-1"></i>
                                        Materia <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="id_materia" name="id_materia" required>
                                        <option value="">Seleccionar materia...</option>
                                        <?php foreach ($materias as $materia): ?>
                                            <option value="<?php echo $materia['id_materia']; ?>">
                                                <?php echo htmlspecialchars($materia['nombre_materia']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (empty($materias)): ?>
                                        <div class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            No hay materias disponibles. Contacta al administrador.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    Descripción (Opcional)
                                </label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                          placeholder="Descripción del examen o instrucciones adicionales..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_preguntas" class="form-label">
                                        <i class="fas fa-list-ol me-1"></i>
                                        Número de Preguntas
                                    </label>
                                    <select class="form-select" id="total_preguntas" name="total_preguntas">
                                        <option value="5">5 preguntas</option>
                                        <option value="10" selected>10 preguntas</option>
                                        <option value="15">15 preguntas</option>
                                        <option value="20">20 preguntas</option>
                                        <option value="25">25 preguntas</option>
                                        <option value="30">30 preguntas</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="dificultad" class="form-label">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Nivel de Dificultad
                                    </label>
                                    <select class="form-select" id="dificultad" name="dificultad">
                                        <option value="mixto" selected>Mixto (Recomendado)</option>
                                        <option value="facil">Fácil</option>
                                        <option value="medio">Medio</option>
                                        <option value="dificil">Difícil</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="generar_examen" class="btn btn-primary btn-lg btn-loading" id="generateBtn">
                                    <span class="btn-text">
                                        <i class="fas fa-magic me-2"></i>
                                        Generar Examen
                                    </span>
                                    <span class="loading">
                                        <i class="fas fa-spinner fa-spin me-2"></i>
                                        Generando...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel lateral -->
            <div class="col-lg-4">
                <!-- Estadísticas rápidas -->
                <div class="card shadow card-hover mb-4">
                    <div class="card-body stats-card text-center">
                        <h5 class="card-title">
                            <i class="fas fa-chart-pie me-2"></i>
                            Estadísticas
                        </h5>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end border-white">
                                    <h3><?php echo count($examenes_recientes); ?></h3>
                                    <small>Exámenes Recientes</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h3><?php echo count($materias); ?></h3>
                                <small>Materias Disponibles</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exámenes recientes -->
                <div class="card shadow card-hover">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Exámenes Recientes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($examenes_recientes)): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                <p class="mb-0">No has generado exámenes aún</p>
                                <small>¡Crea tu primer examen!</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($examenes_recientes as $examen): ?>
                                <div class="exam-card card mb-2">
                                    <div class="card-body py-2">
                                        <h6 class="card-title mb-1">
                                            <a href="ver_examen.php?id=<?php echo $examen['id_examen']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($examen['titulo']); ?>
                                            </a>
                                        </h6>
                                        <p class="card-text small text-muted mb-1">
                                            <i class="fas fa-book me-1"></i>
                                            <?php echo htmlspecialchars($examen['nombre_materia']); ?>
                                            <span class="ms-2">
                                                <i class="fas fa-list me-1"></i>
                                                <?php echo $examen['total_preguntas']; ?> preguntas
                                            </span>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($examen['fecha_generacion'])); ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3">
                                <a href="mis_examenes.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>
                                    Ver Todos
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">
                    <i class="fas fa-bolt text-warning me-2"></i>
                    Accesos Rápidos
                </h3>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 text-center card-hover">
                    <div class="card-body">
                        <i class="fas fa-plus-circle fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Agregar Pregunta</h5>
                        <p class="card-text">Añade nuevas preguntas al banco de datos</p>
                        <a href="agregar_pregunta.php" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            Agregar
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 text-center card-hover">
                    <div class="card-body">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Mis Exámenes</h5>
                        <p class="card-text">Revisa y gestiona tus exámenes guardados</p>
                        <a href="mis_examenes.php" class="btn btn-primary">
                            <i class="fas fa-folder-open me-1"></i>
                            Ver Exámenes
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 text-center card-hover">
                    <div class="card-body">
                        <i class="fas fa-download fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Generar PDF</h5>
                        <p class="card-text">Exporta tus exámenes a formato PDF</p>
                        <button class="btn btn-info" onclick="generarPDFRapido()">
                            <i class="fas fa-file-pdf me-1"></i>
                            Generar
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card h-100 text-center card-hover">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Estadísticas</h5>
                        <p class="card-text">Ve el historial y estadísticas de uso</p>
                        <a href="estadisticas.php" class="btn btn-warning">
                            <i class="fas fa-chart-bar me-1"></i>
                            Ver Stats
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación mejorada del formulario
        function validateForm() {
            const titulo = document.getElementById('titulo').value.trim();
            const materia = document.getElementById('id_materia').value;
            const preguntas = document.getElementById('total_preguntas').value;
            
            // Validaciones
            if (!titulo) {
                alert('Por favor ingresa un título para el examen');
                document.getElementById('titulo').focus();
                return false;
            }
            
            if (titulo.length < 5) {
                alert('El título debe tener al menos 5 caracteres');
                document.getElementById('titulo').focus();
                return false;
            }
            
            if (!materia) {
                alert('Por favor selecciona una materia');
                document.getElementById('id_materia').focus();
                return false;
            }
            
            if (!preguntas || preguntas < 1) {
                alert('Selecciona un número válido de preguntas');
                document.getElementById('total_preguntas').focus();
                return false;
            }
            
            // Mostrar estado de carga
            showLoading();
            return true;
        }

        function showLoading() {
            const btn = document.getElementById('generateBtn');
            const btnText = btn.querySelector('.btn-text');
            const loading = btn.querySelector('.loading');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            loading.style.display = 'inline-block';
        }

        function hideLoading() {
            const btn = document.getElementById('generateBtn');
            const btnText = btn.querySelector('.btn-text');
            const loading = btn.querySelector('.loading');
            
            btn.disabled = false;
            btnText.style.display = 'inline-block';
            loading.style.display = 'none';
        }

        // Auto-completar título basado en materia
        document.getElementById('id_materia').addEventListener('change', function() {
            const materiaText = this.options[this.selectedIndex].text;
            const tituloInput = document.getElementById('titulo');
            
            if (!tituloInput.value && materiaText !== 'Seleccionar materia...') {
                const fecha = new Date().toLocaleDateString('es-ES');
                tituloInput.value = `Examen de ${materiaText} - ${fecha}`;
            }
        });

        function generarPDFRapido() {
            const examenes = <?php echo json_encode($examenes_recientes); ?>;
            if (examenes.length === 0) {
                alert('Primero debes generar al menos un examen');
                return;
            }
            
            const ultimoExamen = examenes[0];
            if (confirm(`¿Generar PDF del examen "${ultimoExamen.titulo}"?`)) {
                window.open(`generar_pdf.php?id=${ultimoExamen.id_examen}`, '_blank');
            }
        }

        // Restaurar botón si hay error de red
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 100);
        });

        // Debug: mostrar datos del formulario antes de enviar
        document.getElementById('examForm').addEventListener('submit', function(e) {
            console.log('Datos del formulario:');
            console.log('Título:', document.getElementById('titulo').value);
            console.log('Materia:', document.getElementById('id_materia').value);
            console.log('Preguntas:', document.getElementById('total_preguntas').value);
            console.log('Dificultad:', document.getElementById('dificultad').value);
        });
    </script>
</body>
</html>