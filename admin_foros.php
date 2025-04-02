<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    // Redirigir si no es administrador
    header("Location: principal.php");
    exit();
}

// Incluir la configuración de la base de datos
require_once 'config/database.php';

// Manejar la creación de un nuevo foro
if (isset($_POST['crear_foro'])) {
    $id = strtolower(trim($_POST['id']));
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $icono = trim($_POST['icono']);
    
    // Validar datos
    $errores = [];
    if (empty($id)) {
        $errores[] = "El ID del foro es obligatorio";
    } elseif (!preg_match('/^[a-z0-9_]+$/', $id)) {
        $errores[] = "El ID solo puede contener letras minúsculas, números y guiones bajos";
    }
    
    if (empty($nombre)) {
        $errores[] = "El nombre del foro es obligatorio";
    }
    
    if (empty($descripcion)) {
        $errores[] = "La descripción del foro es obligatoria";
    }
    
    if (empty($icono)) {
        $errores[] = "El icono del foro es obligatorio";
    }
    
    // Si no hay errores, crear el foro
    if (empty($errores)) {
        try {
            // Verificar si la tabla existe
            $tableExists = false;
            try {
                $stmt = $pdo->query("SELECT 1 FROM foros LIMIT 1");
                $tableExists = true;
            } catch (PDOException $e) {
                // La tabla no existe
            }
            
            // Crear la tabla si no existe
            if (!$tableExists) {
                $pdo->exec("CREATE TABLE foros (
                    id VARCHAR(50) PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    descripcion TEXT NOT NULL,
                    icono VARCHAR(10) NOT NULL,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            // Verificar si ya existe un foro con este ID
            $stmt = $pdo->prepare("SELECT id FROM foros WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                $errores[] = "Ya existe un foro con este ID";
            } else {
                // Insertar nuevo foro
                $stmt = $pdo->prepare("INSERT INTO foros (id, nombre, descripcion, icono) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $nombre, $descripcion, $icono]);
                
                // Crear archivo de foro
                $foro_file = <<<PHP
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';

// Verificar que el usuario ha iniciado sesión
if (!isset(\$_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Configuración específica para este foro
\$foro_id = '$id';
\$foro_nombre = '$nombre';
\$foro_descripcion = '$descripcion';

// Función para obtener el nombre de usuario
function obtenerNombreUsuario(\$pdo, \$user_id) {
    try {
        \$stmt = \$pdo->prepare("SELECT nombre_usuario FROM usuarios WHERE id = ?");
        \$stmt->execute([\$user_id]);
        \$usuario = \$stmt->fetch(PDO::FETCH_ASSOC);
        return \$usuario ? \$usuario['nombre_usuario'] : 'Usuario_'.\$user_id;
    } catch (PDOException \$e) {
        return 'Usuario_'.\$user_id;
    }
}

// Procesar el envío de un nuevo mensaje
if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_POST['message']) && !empty(\$_POST['message'])) {
    \$user_id = \$_SESSION['user_id'];
    \$username = obtenerNombreUsuario(\$pdo, \$user_id);
    \$message = htmlspecialchars(\$_POST['message']);
    \$categoria = \$foro_id;
    
    try {
        \$stmt = \$pdo->prepare("INSERT INTO forum_messages (user_id, username, message, categoria) VALUES (?, ?, ?, ?)");
        \$stmt->execute([\$user_id, \$username, \$message, \$categoria]);
        
        // Redireccionar para evitar reenvío del formulario
        header("Location: foro_".\$foro_id.".php");
        exit;
    } catch (PDOException \$e) {
        \$error_message = "Error al guardar el mensaje: " . \$e->getMessage();
    }
}

// Obtener mensajes específicos de este foro
try {
    // Verificar si existe el campo destacado
    \$check = \$pdo->query("SHOW COLUMNS FROM forum_messages LIKE 'destacado'");
    \$tiene_destacados = \$check->rowCount() > 0;
    
    if (\$tiene_destacados) {
        // Primero obtener los mensajes destacados
        \$stmt = \$pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? AND destacado = 1 ORDER BY created_at DESC");
        \$stmt->execute([\$foro_id]);
        \$destacados = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Luego obtener el resto de mensajes
        \$stmt = \$pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? AND (destacado = 0 OR destacado IS NULL) ORDER BY created_at DESC LIMIT 100");
        \$stmt->execute([\$foro_id]);
        \$normales = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Combinar los mensajes
        \$messages = array_merge(\$destacados, \$normales);
    } else {
        \$stmt = \$pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? ORDER BY created_at DESC LIMIT 100");
        \$stmt->execute([\$foro_id]);
        \$messages = \$stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException \$e) {
    \$error_message = "Error al cargar mensajes: " . \$e->getMessage();
    \$messages = [];
}

// Obtener usuarios activos (últimos 5 minutos)
try {
    \$stmt = \$pdo->query("SELECT DISTINCT nombre_usuario FROM usuarios WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    \$activeUsers = \$stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException \$e) {
    \$error_message = "Error al cargar usuarios activos: " . \$e->getMessage();
    \$activeUsers = [];
}

// Actualizar actividad del usuario
try {
    \$stmt = \$pdo->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = ?");
    \$stmt->execute([\$_SESSION['user_id']]);
} catch (PDOException \$e) {
    // Error silencioso
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(\$foro_nombre); ?> - Proyecto Antena Pringles</title>
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
            <?php echo htmlspecialchars(\$foro_nombre); ?>
        </div>
        
        <h1 class="page-title"><?php echo htmlspecialchars(\$foro_nombre); ?></h1>
        <div class="forum-description"><?php echo htmlspecialchars(\$foro_descripcion); ?></div>
        
        <?php if (isset(\$error_message)): ?>
            <div class="error-message">
                <?php echo \$error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="active-users">
            <h3>Usuarios activos</h3>
            <?php if (empty(\$activeUsers)): ?>
                <p>No hay usuarios activos en este momento.</p>
            <?php else: ?>
                <?php foreach (\$activeUsers as \$user): ?>
                    <span class="user-badge"><?php echo htmlspecialchars(\$user); ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <form class="message-form" method="POST" action="">
            <h3>Escribe un nuevo mensaje</h3>
            <textarea class="message-input" name="message" placeholder="Escribe tu mensaje aquí..."></textarea>
            <button type="submit" class="submit-btn">Publicar mensaje</button>
        </form>
        
        <h2>Mensajes recientes</h2>
        <?php if (empty(\$messages)): ?>
            <div class="no-messages">
                <p>No hay mensajes todavía en este foro. ¡Sé el primero en escribir!</p>
            </div>
        <?php else: ?>
            <?php foreach (\$messages as \$message): ?>
                <?php \$destacado = isset(\$message['destacado']) && \$message['destacado'] == 1; ?>
                <div class="message <?php echo \$destacado ? 'highlighted' : ''; ?>">
                    <div class="message-header">
                        <span>
                            <?php echo htmlspecialchars(\$message['username']); ?>
                            <?php if (\$destacado): ?>
                                <span class="highlight-badge"><i class="fas fa-star"></i> Destacado</span>
                            <?php endif; ?>
                        </span>
                        <span><?php echo date('d/m/Y H:i', strtotime(\$message['created_at'])); ?></span>
                    </div>
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars(\$message['message'])); ?>
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
PHP;
                
                file_put_contents("foro_$id.php", $foro_file);
                
                $mensaje = "Foro creado correctamente y archivo foro_$id.php generado";
                $tipo_mensaje = "success";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear el foro: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "danger";
    }
}

// Manejar la edición de un foro
if (isset($_POST['editar_foro'])) {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $icono = trim($_POST['icono']);
    
    // Validar datos
    $errores = [];
    if (empty($nombre)) {
        $errores[] = "El nombre del foro es obligatorio";
    }
    
    if (empty($descripcion)) {
        $errores[] = "La descripción del foro es obligatoria";
    }
    
    if (empty($icono)) {
        $errores[] = "El icono del foro es obligatorio";
    }
    
    // Si no hay errores, actualizar el foro
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("UPDATE foros SET nombre = ?, descripcion = ?, icono = ? WHERE id = ?");
            $stmt->execute([$nombre, $descripcion, $icono, $id]);
            
            // Actualizar el archivo del foro
            if (file_exists("foro_$id.php")) {
                $foro_content = file_get_contents("foro_$id.php");
                $foro_content = preg_replace('/\$foro_nombre = \'(.*?)\';/', "\$foro_nombre = '$nombre';", $foro_content);
                $foro_content = preg_replace('/\$foro_descripcion = \'(.*?)\';/', "\$foro_descripcion = '$descripcion';", $foro_content);
                file_put_contents("foro_$id.php", $foro_content);
            }
            
            $mensaje = "Foro actualizado correctamente";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar el foro: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "danger";
    }
}

// Manejar la eliminación de un foro
if (isset($_POST['eliminar_foro'])) {
    $id = $_POST['id'];
    
    try {
        // Primero eliminar los mensajes del foro
        $stmt = $pdo->prepare("DELETE FROM forum_messages WHERE categoria = ?");
        $stmt->execute([$id]);
        
        // Luego eliminar el foro
        $stmt = $pdo->prepare("DELETE FROM foros WHERE id = ?");
        $stmt->execute([$id]);
        
        // Eliminar el archivo del foro si existe
        if (file_exists("foro_$id.php")) {
            unlink("foro_$id.php");
        }
        
        $mensaje = "Foro eliminado correctamente";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el foro: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener la lista de foros
try {
    $tableExists = false;
    try {
        $stmt = $pdo->query("SELECT 1 FROM foros LIMIT 1");
        $tableExists = true;
    } catch (PDOException $e) {
        // La tabla no existe
    }
    
    if ($tableExists) {
        $stmt = $pdo->query("SELECT * FROM foros ORDER BY nombre");
        $foros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Crear tabla de foros si no existe
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS foros (
                id VARCHAR(50) PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                descripcion TEXT NOT NULL,
                icono VARCHAR(10) NOT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Insertar foros predeterminados basados en los archivos existentes
            $foros_default = [
                ['id' => 'general', 'nombre' => 'Foro General', 'descripcion' => 'Discusión general sobre cualquier tema relacionado con el proyecto', 'icono' => '📢'],
                ['id' => 'ASIR', 'nombre' => 'ASIR', 'descripcion' => 'Dudas que puedan tener a lo largo del curso', 'icono' => '🖥️'],
                ['id' => 'sugerencias', 'nombre' => 'Sugerencias', 'descripcion' => 'Propuestas de mejora y nuevas ideas para el proyecto', 'icono' => '💡']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO foros (id, nombre, descripcion, icono) VALUES (?, ?, ?, ?)");
            
            foreach ($foros_default as $foro) {
                $stmt->execute([$foro['id'], $foro['nombre'], $foro['descripcion'], $foro['icono']]);
            }
            
            $mensaje = "Se ha creado la tabla de foros con datos predeterminados";
            $tipo_mensaje = "success";
            
            // Obtener foros nuevamente
            $stmt = $pdo->query("SELECT * FROM foros ORDER BY nombre");
            $foros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e2) {
            $mensaje = "Error al crear la tabla de foros: " . $e2->getMessage();
            $tipo_mensaje = "danger";
            $foros = [];
        }
    }
} catch (PDOException $e) {
    $mensaje = "Error al obtener la lista de foros: " . $e->getMessage();
    $tipo_mensaje = "danger";
    $foros = [];
}

// Verificar si la tabla forum_messages tiene la columna destacado
try {
    $check = $pdo->query("SHOW COLUMNS FROM forum_messages LIKE 'destacado'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE forum_messages ADD COLUMN destacado TINYINT(1) DEFAULT 0");
    }
} catch (PDOException $e) {
    // Crear tabla forum_messages si no existe
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS forum_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            categoria VARCHAR(50) NOT NULL,
            destacado TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (PDOException $e2) {
        // Error silencioso
    }
}

// Estadísticas de mensajes por foro
$estadisticas_foros = [];
try {
    foreach ($foros as $foro) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM forum_messages WHERE categoria = ?");
        $stmt->execute([$foro['id']]);
        $estadisticas_foros[$foro['id']] = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    // Silencioso - si hay error, simplemente no mostramos estadísticas
}

// Obtener usuarios para listar autores de mensajes
try {
    $stmt = $pdo->query("SELECT id, nombre_usuario FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    $usuarios = [];
}

// Obtener información detallada de un foro específico
$foro_seleccionado = null;
$mensajes_foro = [];
if (isset($_GET['ver_foro'])) {
    $foro_id = $_GET['ver_foro'];
    
    try {
        // Obtener información del foro
        $stmt = $pdo->prepare("SELECT * FROM foros WHERE id = ?");
        $stmt->execute([$foro_id]);
        $foro_seleccionado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($foro_seleccionado) {
            // Obtener mensajes del foro
            $stmt = $pdo->prepare("SELECT * FROM forum_messages WHERE categoria = ? ORDER BY created_at DESC");
            $stmt->execute([$foro_id]);
            $mensajes_foro = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $mensaje = "Error al obtener la información del foro: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Manejar la eliminación de un mensaje
if (isset($_POST['eliminar_mensaje'])) {
    $mensaje_id = $_POST['mensaje_id'];
    $foro_id = $_POST['foro_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM forum_messages WHERE id = ?");
        $stmt->execute([$mensaje_id]);
        
        $mensaje = "Mensaje eliminado correctamente";
        $tipo_mensaje = "success";
        
        // Redirigir para refrescar la lista de mensajes
        header("Location: admin_foros.php?ver_foro=" . $foro_id);
        exit();
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el mensaje: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Manejar destacar/quitar destacado de un mensaje
if (isset($_POST['destacar_mensaje'])) {
    $mensaje_id = $_POST['mensaje_id'];
    $foro_id = $_POST['foro_id'];
    $estado = isset($_POST['estado']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE forum_messages SET destacado = ? WHERE id = ?");
        $stmt->execute([$estado, $mensaje_id]);
        
        $mensaje = $estado ? "Mensaje destacado correctamente" : "Se ha quitado el destacado del mensaje";
        $tipo_mensaje = "success";
        
        // Redirigir para refrescar la lista de mensajes
        header("Location: admin_foros.php?ver_foro=" . $foro_id);
        exit();
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar el mensaje: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Foros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .emoji-picker {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        .emoji-option {
            cursor: pointer;
            font-size: 1.5rem;
            padding: 5px;
            border-radius: 5px;
        }
        .emoji-option:hover, .emoji-option.selected {
            background-color: #e9ecef;
        }
        .foro-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
            margin-bottom: 20px;
        }
        .foro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .foro-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .mensaje-card {
            margin-bottom: 15px;
            border-left: 4px solid #4caf50;
        }
        .mensaje-card.destacado {
            border-left: 4px solid #ffc107;
            background-color: #fffbeb;
        }
        .mensaje-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .mensaje-autor {
            font-weight: bold;
            color: #2e7d32;
        }
        .mensaje-fecha {
            color: #777;
            font-size: 0.85rem;
        }
        .tab-content {
            padding-top: 20px;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 2px solid #4caf50;
            color: #4caf50;
            font-weight: bold;
        }
        .nav-tabs .nav-link {
            color: #333;
        }
        .badge-contador {
            background-color: #4caf50;
            color: white;
        }
	.highlight-badge {
            background-color: #ffc107;
            color: #212121;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .mensaje-destacado {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #ffc107;
            font-size: 1.2rem;
        }
        .mensaje-contenido {
            margin-top: 10px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Encabezado de la página -->
        <div class="admin-header text-center">
            <h1 class="display-5 mb-3">Administración de Foros</h1>
            <p class="lead">Gestiona los foros del sistema y modera los mensajes.</p>
        </div>

        <!-- Mensajes de alerta -->
        <?php if (isset($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Pestañas de navegación -->
        <ul class="nav nav-tabs mb-4" id="forosTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo !isset($_GET['ver_foro']) ? 'active' : ''; ?>" id="lista-tab" data-bs-toggle="tab" data-bs-target="#listaForos" type="button" role="tab" aria-controls="listaForos" aria-selected="true">
                    Foros disponibles
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="crear-tab" data-bs-toggle="tab" data-bs-target="#crearForo" type="button" role="tab" aria-controls="crearForo" aria-selected="false">
                    Crear nuevo foro
                </button>
            </li>
            <?php if ($foro_seleccionado): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo isset($_GET['ver_foro']) ? 'active' : ''; ?>" id="mensajes-tab" data-bs-toggle="tab" data-bs-target="#mensajesForo" type="button" role="tab" aria-controls="mensajesForo" aria-selected="false">
                    Mensajes: <?php echo htmlspecialchars($foro_seleccionado['nombre']); ?>
                </button>
            </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content" id="forosTabContent">
            <!-- Lista de foros -->
            <div class="tab-pane fade <?php echo !isset($_GET['ver_foro']) ? 'show active' : ''; ?>" id="listaForos" role="tabpanel" aria-labelledby="lista-tab">
                <div class="row">
                    <?php if (empty($foros)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                No hay foros creados. Utiliza la pestaña "Crear nuevo foro" para añadir uno.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($foros as $foro): ?>
                            <div class="col-md-4">
                                <div class="card foro-card">
                                    <div class="card-body text-center">
                                        <div class="foro-icon"><?php echo $foro['icono']; ?></div>
                                        <h4 class="card-title"><?php echo htmlspecialchars($foro['nombre']); ?></h4>
                                        <p class="card-text"><?php echo htmlspecialchars($foro['descripcion']); ?></p>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-success badge-contador">
                                                <?php echo isset($estadisticas_foros[$foro['id']]) ? $estadisticas_foros[$foro['id']] : 0; ?> mensajes
                                            </span>
                                            <span class="text-muted small">ID: <?php echo $foro['id']; ?></span>
                                        </div>
                                        <hr>
                                        <div class="btn-group mt-2">
                                            <a href="?ver_foro=<?php echo $foro['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-chat-dots"></i> Ver mensajes
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editarForoModal<?php echo $foro['id']; ?>">
                                                <i class="bi bi-pencil"></i> Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#eliminarForoModal<?php echo $foro['id']; ?>">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal para editar foro -->
                                <div class="modal fade" id="editarForoModal<?php echo $foro['id']; ?>" tabindex="-1" aria-labelledby="editarForoModalLabel<?php echo $foro['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editarForoModalLabel<?php echo $foro['id']; ?>">Editar Foro</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="post" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $foro['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="nombre<?php echo $foro['id']; ?>" class="form-label">Nombre del Foro</label>
                                                        <input type="text" class="form-control" id="nombre<?php echo $foro['id']; ?>" name="nombre" value="<?php echo htmlspecialchars($foro['nombre']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="descripcion<?php echo $foro['id']; ?>" class="form-label">Descripción</label>
                                                        <textarea class="form-control" id="descripcion<?php echo $foro['id']; ?>" name="descripcion" rows="3" required><?php echo htmlspecialchars($foro['descripcion']); ?></textarea>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="icono<?php echo $foro['id']; ?>" class="form-label">Icono (Emoji)</label>
                                                        <input type="text" class="form-control" id="icono<?php echo $foro['id']; ?>" name="icono" value="<?php echo $foro['icono']; ?>" required>
                                                        <div class="emoji-picker" data-target="icono<?php echo $foro['id']; ?>">
                                                            <span class="emoji-option" data-emoji="📢">📢</span>
                                                            <span class="emoji-option" data-emoji="💬">💬</span>
                                                            <span class="emoji-option" data-emoji="🖥️">🖥️</span>
                                                            <span class="emoji-option" data-emoji="💡">💡</span>
                                                            <span class="emoji-option" data-emoji="❓">❓</span>
                                                            <span class="emoji-option" data-emoji="📚">📚</span>
                                                            <span class="emoji-option" data-emoji="🔧">🔧</span>
                                                            <span class="emoji-option" data-emoji="🎮">🎮</span>
                                                            <span class="emoji-option" data-emoji="🎓">🎓</span>
                                                            <span class="emoji-option" data-emoji="🌐">🌐</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" name="editar_foro" class="btn btn-primary">Guardar cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal para eliminar foro -->
                                <div class="modal fade" id="eliminarForoModal<?php echo $foro['id']; ?>" tabindex="-1" aria-labelledby="eliminarForoModalLabel<?php echo $foro['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="eliminarForoModalLabel<?php echo $foro['id']; ?>">Confirmar eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas eliminar el foro <strong><?php echo htmlspecialchars($foro['nombre']); ?></strong>?</p>
                                                <p class="text-danger"><strong>Atención:</strong> Esta acción eliminará también todos los mensajes asociados al foro y no se puede deshacer.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form method="post" action="">
                                                    <input type="hidden" name="id" value="<?php echo $foro['id']; ?>">
                                                    <button type="submit" name="eliminar_foro" class="btn btn-danger">Eliminar Foro</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Crear nuevo foro -->
            <div class="tab-pane fade" id="crearForo" role="tabpanel" aria-labelledby="crear-tab">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Formulario para Crear Nuevo Foro</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id" class="form-label">ID del Foro</label>
                                    <input type="text" class="form-control" id="id" name="id" placeholder="Ejemplo: asir_proyectos" required>
                                    <small class="text-muted">Solo letras minúsculas, números y guiones bajos. Sin espacios ni caracteres especiales.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre del Foro</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ejemplo: Proyectos de ASIR" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe brevemente el propósito de este foro" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="icono" class="form-label">Icono (Emoji)</label>
                                <input type="text" class="form-control" id="icono" name="icono" placeholder="Selecciona un emoji abajo" required>
                                <div class="emoji-picker" data-target="icono">
                                    <span class="emoji-option" data-emoji="📢">📢</span>
                                    <span class="emoji-option" data-emoji="💬">💬</span>
                                    <span class="emoji-option" data-emoji="🖥️">🖥️</span>
                                    <span class="emoji-option" data-emoji="💡">💡</span>
                                    <span class="emoji-option" data-emoji="❓">❓</span>
                                    <span class="emoji-option" data-emoji="📚">📚</span>
                                    <span class="emoji-option" data-emoji="🔧">🔧</span>
                                    <span class="emoji-option" data-emoji="🎮">🎮</span>
                                    <span class="emoji-option" data-emoji="🎓">🎓</span>
                                    <span class="emoji-option" data-emoji="🌐">🌐</span>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" name="crear_foro" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Crear Foro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Mensajes del foro -->
            <?php if ($foro_seleccionado): ?>
            <div class="tab-pane fade <?php echo isset($_GET['ver_foro']) ? 'show active' : ''; ?>" id="mensajesForo" role="tabpanel" aria-labelledby="mensajes-tab">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-5"><?php echo $foro_seleccionado['icono']; ?> <?php echo htmlspecialchars($foro_seleccionado['nombre']); ?></span>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($foro_seleccionado['descripcion']); ?></p>
                        </div>
                        <a href="admin_foros.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver a la lista
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($mensajes_foro)): ?>
                            <div class="alert alert-info">
                                No hay mensajes en este foro.
                            </div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($mensajes_foro as $mensaje): ?>
                                    <?php $destacado = isset($mensaje['destacado']) && $mensaje['destacado'] == 1; ?>
                                    <div class="card mensaje-card p-3 mb-3 <?php echo $destacado ? 'destacado' : ''; ?>">
                                        <?php if ($destacado): ?>
                                            <div class="mensaje-destacado">
                                                <i class="bi bi-star-fill"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mensaje-header">
                                            <span class="mensaje-autor">
                                                <?php echo isset($usuarios[$mensaje['user_id']]) ? htmlspecialchars($usuarios[$mensaje['user_id']]) : htmlspecialchars($mensaje['username']); ?>
                                            </span>
                                            <span class="mensaje-fecha">
                                                <?php echo date('d/m/Y H:i', strtotime($mensaje['created_at'])); ?>
                                            </span>
                                        </div>
                                        <div class="mensaje-contenido">
                                            <?php echo nl2br(htmlspecialchars($mensaje['message'])); ?>
                                        </div>
                                        <div class="d-flex justify-content-end mt-2 gap-2">
                                            <form method="post" action="">
                                                <input type="hidden" name="mensaje_id" value="<?php echo $mensaje['id']; ?>">
                                                <input type="hidden" name="foro_id" value="<?php echo $foro_seleccionado['id']; ?>">
                                                <input type="hidden" name="estado" value="<?php echo $destacado ? '0' : '1'; ?>">
                                                <button type="submit" name="destacar_mensaje" class="btn btn-sm btn-outline-warning">
                                                    <i class="bi bi-star<?php echo $destacado ? '-fill' : ''; ?>"></i> 
                                                    <?php echo $destacado ? 'Quitar destacado' : 'Destacar mensaje'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="post" action="" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este mensaje?');">
                                                <input type="hidden" name="mensaje_id" value="<?php echo $mensaje['id']; ?>">
                                                <input type="hidden" name="foro_id" value="<?php echo $foro_seleccionado['id']; ?>">
                                                <button type="submit" name="eliminar_mensaje" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Eliminar mensaje
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Botones de navegación -->
        <div class="text-center mt-5">
            <a href="admin.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Volver al Panel de Administración
            </a>
            <a href="principal.php" class="btn btn-outline-primary" onclick="sessionStorage.removeItem('currentSection');">
                <i class="bi bi-house"></i> Ir al Menú Principal
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Activar pestañas
            if (window.location.href.includes('ver_foro')) {
                document.getElementById('mensajes-tab').click();
            }
            
            // Selector de emojis
            document.querySelectorAll('.emoji-option').forEach(function(emoji) {
                emoji.addEventListener('click', function() {
                    const targetId = this.parentElement.getAttribute('data-target');
                    const targetInput = document.getElementById(targetId);
                    targetInput.value = this.getAttribute('data-emoji');
                    
                    // Resaltar el emoji seleccionado
                    this.parentElement.querySelectorAll('.emoji-option').forEach(function(e) {
                        e.classList.remove('selected');
                    });
                    this.classList.add('selected');
                });
            });
            
            // Inicializar emojis ya seleccionados
            document.querySelectorAll('.emoji-picker').forEach(function(picker) {
                const targetId = picker.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const selectedEmoji = targetInput.value;
                
                if (selectedEmoji) {
                    picker.querySelectorAll('.emoji-option').forEach(function(emoji) {
                        if (emoji.getAttribute('data-emoji') === selectedEmoji) {
                            emoji.classList.add('selected');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
