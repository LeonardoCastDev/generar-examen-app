<?php
// Activar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar sesiones antes de iniciarlas
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerar ID de sesión para mayor seguridad
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'generador_examenes');
define('DB_USER', 'root');
define('DB_PASS', ''); // En XAMPP por defecto está vacío

// Configuración de la aplicación
define('SITE_URL', 'http://localhost/generador-examenes/');
define('SITE_NAME', 'Generador de Exámenes');

// Variable global para la conexión
$pdo = null;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Verificar que la conexión funcione
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    // Mostrar error de conexión detallado
    die("
    <div style='background: #ffebee; border: 1px solid #e57373; padding: 20px; margin: 20px; border-radius: 5px;'>
        <h3 style='color: #c62828;'>Error de Conexión a la Base de Datos</h3>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <p><strong>Código:</strong> " . $e->getCode() . "</p>
        <h4>Posibles soluciones:</h4>
        <ul>
            <li>Verificar que MySQL esté corriendo en XAMPP</li>
            <li>Verificar que la base de datos 'generador_examenes' existe</li>
            <li>Verificar las credenciales de conexión</li>
        </ul>
    </div>
    ");
}

// Funciones útiles
function redirect($url) {
    // Verificar si ya se enviaron headers
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        // Si ya se enviaron headers, usar JavaScript
        echo "<script>window.location.href = '$url';</script>";
        exit();
    }
}

function showMessage($message, $type = 'info') {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['message'] = [
            'message' => $message,
            'type' => $type
        ];
    }
}

function getMessage() {
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

function isLoggedIn() {
    // Verificar que la sesión esté activa y que existan las variables necesarias
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }
    
    // Verificar que existan las variables de sesión requeridas
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        return false;
    }
    
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        return false;
    }
    
    // Verificar que el user_id sea numérico
    if (!is_numeric($_SESSION['user_id'])) {
        return false;
    }
    
    return true;
}

function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn() || !$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ? AND activo = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        // Si no se encuentra el usuario, destruir la sesión
        if (!$user) {
            session_unset();
            session_destroy();
            return null;
        }
        
        return $user;
    } catch (PDOException $e) {
        // Log error pero no romper la aplicación
        error_log("Error obteniendo usuario actual: " . $e->getMessage());
        return null;
    }
}

// Función para requerir autenticación
function requireAuth() {
    if (!isLoggedIn()) {
        showMessage('Debes iniciar sesión para acceder a esta página', 'error');
        redirect('login.php');
        exit();
    }
}

// Función para limpiar datos de sesión inválidos
function cleanSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Si hay datos de sesión pero el usuario no está validado correctamente
        if ((isset($_SESSION['user_id']) || isset($_SESSION['username'])) && !isLoggedIn()) {
            session_unset();
            session_destroy();
            session_start();
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
}

// Limpiar sesión al cargar config
cleanSession();
?>