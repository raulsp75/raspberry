<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Redirigir si no es administrador
    header("Location: principal.php");
    exit();
}

// Obtener nombre del usuario
$nombre_usuario = $_SESSION['user'] ?? "Administrador";

// Configuración de base de datos para las estadísticas
require_once 'config/database.php';

// Obtener estadísticas básicas
try {
    // Total de usuarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $stmt->fetchColumn();
    
    // Total de admins
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
    $total_admins = $stmt->fetchColumn();
    
    // Total de usuarios bloqueados
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE bloqueado = 1");
    $total_bloqueados = $stmt->fetchColumn();
    
    // Total de mensajes en el foro
    $stmt = $pdo->query("SELECT COUNT(*) FROM forum_messages");
    $total_mensajes = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Error silencioso
    $total_usuarios = 0;
    $total_admins = 0;
    $total_bloqueados = 0;
    $total_mensajes = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .admin-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
        }
        .admin-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .admin-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .stats-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .welcome-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Sección de bienvenida -->
        <div class="welcome-section text-center">
            <h1 class="display-4 mb-3">Panel de Administración</h1>
            <p class="lead">Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong>. Desde aquí puedes gestionar todos los aspectos de la plataforma.</p>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill admin-icon"></i>
                        <h3 class="card-title"><?php echo $total_usuarios; ?></h3>
                        <p class="card-text">Usuarios Registrados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-lock-fill admin-icon"></i>
                        <h3 class="card-title"><?php echo $total_admins; ?></h3>
                        <p class="card-text">Administradores</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-slash-circle-fill admin-icon"></i>
                        <h3 class="card-title"><?php echo $total_bloqueados; ?></h3>
                        <p class="card-text">Usuarios Bloqueados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-chat-left-text-fill admin-icon"></i>
                        <h3 class="card-title"><?php echo $total_mensajes; ?></h3>
                        <p class="card-text">Mensajes en Foros</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Opciones de administración -->
        <h2 class="mb-4 text-center">Gestión del Sistema</h2>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card admin-card">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill admin-icon text-primary"></i>
                        <h4 class="card-title">Gestión de Usuarios</h4>
                        <p class="card-text">Administra usuarios, roles y permisos. Bloquea o desbloquea cuentas.</p>
                        <a href="admin_usuarios.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card admin-card">
                    <div class="card-body text-center">
                        <i class="bi bi-chat-square-text-fill admin-icon text-success"></i>
                        <h4 class="card-title">Gestión de Foros</h4>
                        <p class="card-text">Modera mensajes, crea nuevos foros o edita la configuración existente.</p>
                        <a href="admin_foros.php" class="btn btn-success">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card admin-card">
                    <div class="card-body text-center">
                        <i class="bi bi-gear-fill admin-icon text-secondary"></i>
                        <h4 class="card-title">Configuración General</h4>
                        <p class="card-text">Configura parámetros generales del sistema y opciones avanzadas.</p>
                        <a href="admin_config.php" class="btn btn-secondary">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="principal.php" class="btn btn-lg btn-outline-secondary" onclick="sessionStorage.removeItem('currentSection');">
                <i class="bi bi-arrow-left"></i> Volver al Menú Principal
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
