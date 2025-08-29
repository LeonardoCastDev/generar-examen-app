<?php
// Activar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Diagnóstico del Sistema</h2>";

// Verificar PHP
echo "<h3>1. PHP está funcionando: ✓</h3>";
echo "Versión PHP: " . phpversion() . "<br>";

// Verificar extensiones necesarias
echo "<h3>2. Extensiones PHP:</h3>";
echo "PDO: " . (extension_loaded('pdo') ? '✓' : '✗') . "<br>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✓' : '✗') . "<br>";

// Intentar conexión a base de datos
echo "<h3>3. Conexión a Base de Datos:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=generador_examenes", "root", "");
    echo "Conexión exitosa ✓<br>";
    
    // Verificar si existen las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . count($tables) . "<br>";
    foreach($tables as $table) {
        echo "- $table<br>";
    }
    
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "<br>";
}

// Verificar archivos
echo "<h3>4. Archivos del sistema:</h3>";
$archivos = ['config.php', 'auth.php', 'ExamGenerator.php', 'login.php', 'index.php'];
foreach($archivos as $archivo) {
    echo "$archivo: " . (file_exists($archivo) ? '✓' : '✗') . "<br>";
}

// Verificar permisos de sesión
echo "<h3>5. Sesiones:</h3>";
echo "Session save path: " . session_save_path() . "<br>";
echo "Session start: ";
if(session_start()) {
    echo "✓<br>";
} else {
    echo "✗<br>";
}
?>