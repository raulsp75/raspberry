<?php
session_start();
require_once 'config/database.php'; // Archivo de conexión a la BD

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Procesar el envío de un nuevo mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && !empty($_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['nombre_usuario']; // Cambiado a nombre_usuario según tu BD
    $message = htmlspecialchars($_POST['message']);
    
    $stmt = $pdo->prepare("INSERT INTO forum_messages (user_id, username, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $username, $message]);
}

// Obtener todos los mensajes
$stmt = $pdo->query("SELECT * FROM forum_messages ORDER BY created_at DESC LIMIT 100");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener usuarios activos (últimos 5 minutos)
$stmt = $pdo->query("SELECT DISTINCT nombre_usuario FROM usuarios WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$activeUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro - Proyecto Antena Pringles</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .forum-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .message {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .message-content {
            padding: 5px 0;
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
        }
        .message-input {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            min-height: 80px;
            margin-bottom: 10px;
        }
        .submit-btn {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="forum-container">
        <h1>Foro de Usuarios</h1>
        
        <div class="active-users">
            <h3>Usuarios activos</h3>
            <?php foreach ($activeUsers as $user): ?>
                <span class="user-badge"><?php echo $user; ?></span>
            <?php endforeach; ?>
        </div>
        
        <form class="message-form" method="POST" action="">
            <textarea class="message-input" name="message" placeholder="Escribe tu mensaje aquí..."></textarea>
            <button type="submit" class="submit-btn">Enviar mensaje</button>
        </form>
        
        <h2>Mensajes recientes</h2>
        <?php if (empty($messages)): ?>
            <p>No hay mensajes todavía. ¡Sé el primero en escribir!</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <div class="message-header">
                        <span><?php echo $message['username']; ?></span>
                        <span><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></span>
                    </div>
                    <div class="message-content">
                        <?php echo $message['message']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        // Script para actualizar la página cada 30 segundos para ver nuevos mensajes
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Actualizar el último tiempo de actividad del usuario
        fetch('update_activity.php', {
            method: 'POST'
        });
    </script>
</body>
</html>


// update_activity.php - Script para actualizar la actividad del usuario
<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}
?>
