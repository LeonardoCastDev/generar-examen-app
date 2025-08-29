<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function verificarBaseDatos() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=generador_examenes;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar si existen las tablas principales
        $tablas_necesarias = ['usuarios', 'materias', 'banco_preguntas', 'examenes_generados'];
        $tablas_existentes = [];
        
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tablas_existentes[] = $row[0];
        }
        
        $faltan_tablas = array_diff($tablas_necesarias, $tablas_existentes);
        
        return [
            'conectado' => true,
            'tablas_existentes' => $tablas_existentes,
            'faltan_tablas' => $faltan_tablas,
            'pdo' => $pdo
        ];
    } catch (Exception $e) {
        return [
            'conectado' => false,
            'error' => $e->getMessage()
        ];
    }
}

function crearTablasBasicas($pdo) {
    $sql = "
    -- Tabla usuarios
    CREATE TABLE IF NOT EXISTS usuarios (
        id_usuario INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultimo_acceso TIMESTAMP NULL,
        activo BOOLEAN DEFAULT TRUE
    );

    -- Tabla materias
    CREATE TABLE IF NOT EXISTS materias (
        id_materia INT PRIMARY KEY AUTO_INCREMENT,
        nombre_materia VARCHAR(100) NOT NULL,
        descripcion TEXT,
        activa BOOLEAN DEFAULT TRUE,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Banco de preguntas
    CREATE TABLE IF NOT EXISTS banco_preguntas (
        id_pregunta_banco INT PRIMARY KEY AUTO_INCREMENT,
        id_materia INT NOT NULL,
        enunciado TEXT NOT NULL,
        tipo_pregunta ENUM('multiple', 'verdadero_falso', 'abierta') NOT NULL,
        nivel_dificultad ENUM('facil', 'medio', 'dificil') DEFAULT 'medio',
        puntuacion DECIMAL(3,1) DEFAULT 1.0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        activa BOOLEAN DEFAULT TRUE,
        
        FOREIGN KEY (id_materia) REFERENCES materias(id_materia)
    );

    -- Exámenes generados
    CREATE TABLE IF NOT EXISTS examenes_generados (
        id_examen INT PRIMARY KEY AUTO_INCREMENT,
        id_usuario INT NOT NULL,
        id_materia INT NOT NULL,
        titulo VARCHAR(200) NOT NULL,
        descripcion TEXT,
        total_preguntas INT DEFAULT 10,
        nivel_dificultad ENUM('facil', 'medio', 'dificil', 'mixto') DEFAULT 'mixto',
        fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_guardado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        activo BOOLEAN DEFAULT TRUE,
        
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
        FOREIGN KEY (id_materia) REFERENCES materias(id_materia)
    );

    -- Preguntas del examen
    CREATE TABLE IF NOT EXISTS preguntas_examen (
        id_pregunta_examen INT PRIMARY KEY AUTO_INCREMENT,
        id_examen INT NOT NULL,
        id_pregunta_banco INT NOT NULL,
        numero_pregunta INT NOT NULL,
        puntuacion_asignada DECIMAL(3,1) DEFAULT 1.0,
        
        FOREIGN KEY (id_examen) REFERENCES examenes_generados(id_examen) ON DELETE CASCADE,
        FOREIGN KEY (id_pregunta_banco) REFERENCES banco_preguntas(id_pregunta_banco)
    );

    -- Opciones para preguntas múltiples
    CREATE TABLE IF NOT EXISTS opciones_banco (
        id_opcion_banco INT PRIMARY KEY AUTO_INCREMENT,
        id_pregunta_banco INT NOT NULL,
        letra_opcion CHAR(1) NOT NULL,
        texto_opcion TEXT NOT NULL,
        es_correcta BOOLEAN DEFAULT FALSE,
        
        FOREIGN KEY (id_pregunta_banco) REFERENCES banco_preguntas(id_pregunta_banco) ON DELETE CASCADE
    );

    -- Respuestas para preguntas abiertas
    CREATE TABLE IF NOT EXISTS respuestas_banco (
        id_respuesta_banco INT PRIMARY KEY AUTO_INCREMENT,
        id_pregunta_banco INT NOT NULL,
        respuesta_correcta TEXT NOT NULL,
        es_exacta BOOLEAN DEFAULT FALSE,
        
        FOREIGN KEY (id_pregunta_banco) REFERENCES banco_preguntas(id_pregunta_banco) ON DELETE CASCADE
    );
    ";

    // Ejecutar las sentencias SQL una por una
    $statements = explode(';', $sql);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (Exception $e) {
                // Continuar con el siguiente statement aunque uno falle
            }
        }
    }
}

function insertarDatosDemo($pdo) {
    // Insertar materias de ejemplo
    $pdo->exec("
        INSERT IGNORE INTO materias (nombre_materia, descripcion) VALUES
        ('Matemáticas', 'Preguntas de matemáticas básicas y avanzadas'),
        ('Historia', 'Historia universal y nacional'),
        ('Ciencias Naturales', 'Biología, física y química'),
        ('Literatura', 'Literatura clásica y contemporánea'),
        ('Geografía', 'Geografía mundial y local'),
        ('Inglés', 'Gramática y vocabulario en inglés')
    ");

    // Insertar algunas preguntas de ejemplo
    $pdo->exec("
        INSERT IGNORE INTO banco_preguntas (id_materia, enunciado, tipo_pregunta, nivel_dificultad, puntuacion) VALUES
        (1, '¿Cuál es el resultado de 2 + 2?', 'multiple', 'facil', 1.0),
        (1, '¿Es verdadero que 5 > 3?', 'verdadero_falso', 'facil', 1.0),
        (1, 'Resuelve la ecuación: 3x + 5 = 14', 'abierta', 'medio', 2.0)
    ");

    // Insertar opciones para la primera pregunta
    $pdo->exec("
        INSERT IGNORE INTO opciones_banco (id_pregunta_banco, letra_opcion, texto_opcion, es_correcta) VALUES
        (1, 'A', '3', false),
        (1, 'B', '4', true),
        (1, 'C', '5', false),
        (1, 'D', '6', false)
    ");
}

// Página HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalador - Generador de Exámenes</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .success { color: green; background: #e8f5e8; padding: 10px; border-radius: 5px; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; }
        .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Instalador del Sistema Generador de Exámenes</h1>
    
    <?php
    $resultado_bd = verificarBaseDatos();
    
    if (!$resultado_bd['conectado']) {
        echo "<div class='error'>";
        echo "<h3>Error de Conexión a Base de Datos</h3>";
        echo "<p><strong>Error:</strong> " . $resultado_bd['error'] . "</p>";
        echo "<h4>Soluciones:</h4>";
        echo "<ul>";
        echo "<li>Verificar que MySQL esté corriendo en XAMPP</li>";
        echo "<li>Crear la base de datos 'generador_examenes' en phpMyAdmin</li>";
        echo "<li>Verificar las credenciales de conexión</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "<h3>✓ Conexión a Base de Datos Exitosa</h3>";
        echo "</div>";
        
        if (!empty($resultado_bd['faltan_tablas'])) {
            echo "<div class='warning'>";
            echo "<h3>⚠ Faltan Tablas en la Base de Datos</h3>";
            echo "<p>Tablas faltantes: " . implode(', ', $resultado_bd['faltan_tablas']) . "</p>";
            echo "</div>";
            
            if (isset($_POST['crear_tablas'])) {
                try {
                    crearTablasBasicas($resultado_bd['pdo']);
                    insertarDatosDemo($resultado_bd['pdo']);
                    echo "<div class='success'>";
                    echo "<h3>✓ Tablas creadas exitosamente</h3>";
                    echo "<p>El sistema está listo para usar.</p>";
                    echo "<p><a href='login.php' class='btn'>Ir al Login</a></p>";
                    echo "</div>";
                } catch (Exception $e) {
                    echo "<div class='error'>";
                    echo "<h3>Error al crear tablas</h3>";
                    echo "<p>" . $e->getMessage() . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<form method='post'>";
                echo "<button type='submit' name='crear_tablas' class='btn'>Crear Tablas Automáticamente</button>";
                echo "</form>";
            }
        } else {
            echo "<div class='success'>";
            echo "<h3>✓ Todas las tablas están presentes</h3>";
            echo "</div>";
            
            echo "<h3>Tablas en la Base de Datos:</h3>";
            echo "<table>";
            echo "<tr><th>Tabla</th><th>Estado</th></tr>";
            foreach ($resultado_bd['tablas_existentes'] as $tabla) {
                echo "<tr><td>$tabla</td><td>✓ Existe</td></tr>";
            }
            echo "</table>";
            
            echo "<div class='success'>";
            echo "<h3>🎉 Sistema Completamente Configurado</h3>";
            echo "<p>Todo está listo para usar el generador de exámenes.</p>";
            echo "<p><a href='login.php' class='btn'>Ir al Login</a> <a href='index.php' class='btn'>Ir al Sistema</a></p>";
            echo "</div>";
        }
    }
    ?>
    
    <hr>
    <h3>Información del Sistema:</h3>
    <table>
        <tr><th>Componente</th><th>Estado</th></tr>
        <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
        <tr><td>PDO MySQL</td><td><?php echo extension_loaded('pdo_mysql') ? '✓ Disponible' : '✗ No disponible'; ?></td></tr>
        <tr><td>Sessions</td><td><?php echo session_start() ? '✓ Funcionando' : '✗ Error'; ?></td></tr>
        <tr><td>config.php</td><td><?php echo file_exists('config.php') ? '✓ Existe' : '✗ Falta'; ?></td></tr>
        <tr><td>auth.php</td><td><?php echo file_exists('auth.php') ? '✓ Existe' : '✗ Falta'; ?></td></tr>
        <tr><td>ExamGenerator.php</td><td><?php echo file_exists('ExamGenerator.php') ? '✓ Existe' : '✗ Falta'; ?></td></tr>
    </table>
</body>
</html>