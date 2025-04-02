<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Configuración específica para este foro (cambiar para cada foro)
$foro_id = 'ASIR'; // Cambia esto para cada foro (general, tecnico, sugerencias, proyectos)
$foro_nombre = 'ASIR'; // Cambia el nombre para cada foro
$foro_descripcion = 'Dudas que puedan tener a lo largo del curso'; // Cambia la descripción

// Función para obtener el nombre de usuario
function obtenerNombreUsuario($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT nombre_usuario FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario ? $usuario['nombre_usuario'] : 'Usuario_'.$user_id;
    } catch (PDOException $e) {
        return 'Usuario_'.$user_id;
    }
}

// Procesar el envío de un nuevo mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty($_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $username = obtenerNombreUsuario($pdo, $user_id);
    $message = htmlspecialchars($_POST['message']);
    $categoria = $foro_id; // Guardar la categoría del foro
    
    try {
        $stmt = $pdo->prepare("INSERT INTO forum_messages (user_id, username, message, categoria) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $username, $message, $categoria]);
        
        // Redireccionar para evitar reenvío del formulario
        header("Location: foro_".$foro_id.".php");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al guardar el mensaje: " . $e->getMessage();
    }
}

// Obtener mensajes específicos de este foro
try {
    // Verificar si existe el campo destacado
    $check = $pdo->query("SHOW COLUMNS FROM forum_messages LIKE 'destacado'");
    $tiene_destacados = $check->rowCount() > 0;
    
    if ($tiene_destacados) {
        // Primero obtener los mensajes destacados
        $stmt = $pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? AND destacado = 1 ORDER BY created_at DESC");
        $stmt->execute([$foro_id]);
        $destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Luego obtener el resto de mensajes
        $stmt = $pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? AND (destacado = 0 OR destacado IS NULL) ORDER BY created_at DESC LIMIT 100");
        $stmt->execute([$foro_id]);
        $normales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Combinar los mensajes
        $messages = array_merge($destacados, $normales);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? ORDER BY created_at DESC LIMIT 100");
        $stmt->execute([$foro_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_message = "Error al cargar mensajes: " . $e->getMessage();
    $messages = [];
}

// Obtener usuarios activos (últimos 5 minutos)
try {
    $stmt = $pdo->query("SELECT DISTINCT nombre_usuario FROM usuarios WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $activeUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error_message = "Error al cargar usuarios activos: " . $e->getMessage();
    $activeUsers = [];
}

// Actualizar actividad del usuario
try {
    $stmt = $pdo->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Error silencioso
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($foro_nombre); ?> - Proyecto Antena Pringles</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .forum-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .message {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #4caf50;
            position: relative;
        }
        .message.highlighted {
            background-color: #f0f7ff;
            border-left: 4px solid #2196f3;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
            color: #2e7d32;
        }
        .message-content {
            padding: 5px 0;
            line-height: 1.5;
        }
        .active-users {
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .user-badge {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            margin: 2px;
            font-size: 0.8em;
        }
        .message-form {
            margin-top: 20px;
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
        }
        .message-input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            min-height: 100px;
            margin-bottom: 10px;
            font-family: inherit;
        }
        .submit-btn {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .submit-btn:hover {
            background-color: #388e3c;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .forum-description {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-style: italic;
            color: #555;
        }
        .page-title {
            color: #2e7d32;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .breadcrumb {
            background-color: #f5f5f5;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .breadcrumb a {
            color: #2e7d32;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .no-messages {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            color: #555;
        }
        .back-button {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #388e3c;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }
        .highlight-badge {
            background-color: #ffc107;
            color: #212121;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
            margin-left: 5px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="forum-container">
        <div class="breadcrumb">
            <a href="principal.php">Inicio</a> &gt; 
            <a href="foro.php">Foros</a> &gt; 
            <?php echo htmlspecialchars($foro_nombre); ?>
        </div>
        
        <h1 class="page-title"><?php echo htmlspecialchars($foro_nombre); ?></h1>
        <div class="forum-description"><?php echo htmlspecialchars($foro_descripcion); ?></div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="active-users">
            <h3>Usuarios activos</h3>
            <?php if (empty($activeUsers)): ?>
                <p>No hay usuarios activos en este momento.</p>
            <?php else: ?>
                <?php foreach ($activeUsers as $user): ?>
                    <span class="user-badge"><?php echo htmlspecialchars($user); ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <form class="message-form" method="POST" action="">
            <h3>Escribe un nuevo mensaje</h3>
            <textarea class="message-input" name="message" placeholder="Escribe tu mensaje aquí..."></textarea>
            <button type="submit" class="submit-btn">Publicar mensaje</button>
        </form>
        
        <h2>Mensajes recientes</h2>
        <?php if (empty($messages)): ?>
            <div class="no-messages">
                <p>No hay mensajes todavía en este foro. ¡Sé el primero en escribir!</p>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <?php $destacado = isset($message['destacado']) && $message['destacado'] == 1; ?>
                <div class="message <?php echo $destacado ? 'highlighted' : ''; ?>">
                    <div class="message-header">
                        <span>
                            <?php echo htmlspecialchars($message['username']); ?>
                            <?php if ($destacado): ?>
                                <span class="highlight-badge"><i class="fas fa-star"></i> Destacado</span>
                            <?php endif; ?>
                        </span>
                        <span><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></span>
                    </div>
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="button-container">
            <a href="foro.php" class="back-button">← Volver a todos los foros</a>
            <a href="principal.php" class="back-button">Volver al menú principal</a>
        </div>
    </div>
    
    <script>
        // Script para actualizar la página cada 60 segundos para ver nuevos mensajes
        setTimeout(function() {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
