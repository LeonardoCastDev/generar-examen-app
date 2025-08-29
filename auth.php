<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Procesar acciones de autenticación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'register':
            handleRegister();
            break;
        case 'logout':
            handleLogout();
            break;
    }
}

// Manejar logout por GET
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    handleLogout();
}

function handleLogin() {
    global $pdo;
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        showMessage('Por favor, complete todos los campos', 'error');
        return;
    }
    
    try {
        // Buscar usuario por username o email
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login exitoso
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            
            // Actualizar último acceso
            $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id_usuario = ?");
            $stmt->execute([$user['id_usuario']]);
            
            showMessage('¡Bienvenido ' . $user['nombre'] . '!', 'success');
            redirect('index.php');
        } else {
            showMessage('Usuario o contraseña incorrectos', 'error');
        }
    } catch (PDOException $e) {
        showMessage('Error en el sistema: ' . $e->getMessage(), 'error');
    }
}

function handleRegister() {
    global $pdo;
    
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($username) || empty($email) || empty($password)) {
        showMessage('Por favor, complete todos los campos', 'error');
        return;
    }
    
    if (strlen($password) < 6) {
        showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        showMessage('El email no es válido', 'error');
        return;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        showMessage('El nombre de usuario debe tener entre 3 y 20 caracteres y solo puede contener letras, números y guiones bajos', 'error');
        return;
    }
    
    try {
        // Verificar si el usuario o email ya existe
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            showMessage('El nombre de usuario o email ya está registrado', 'error');
            return;
        }
        
        // Crear nuevo usuario
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, email, password_hash, nombre, apellido, fecha_registro) 
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        
        if ($stmt->execute([$username, $email, $password_hash, $nombre, $apellido])) {
            showMessage('¡Registro exitoso! Ya puedes iniciar sesión', 'success');
            redirect('login.php');
        } else {
            showMessage('Error al registrar el usuario', 'error');
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { // Duplicate entry
            showMessage('El nombre de usuario o email ya está registrado', 'error');
        } else {
            showMessage('Error en el sistema: ' . $e->getMessage(), 'error');
        }
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    showMessage('Has cerrado sesión correctamente', 'success');
    redirect('login.php');
}
?>