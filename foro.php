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

// Actualizar la actividad del usuario
try {
    $stmt = $pdo->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Error silencioso
}

// Lista de foros disponibles - puedes modificar esto según tus necesidades
$foros = [
    [
        'id' => 'general',
        'nombre' => 'Foro General',
        'descripcion' => 'Discusión general sobre cualquier tema relacionado con el proyecto',
        'icono' => '📢'
    ],
    [
        'id' => 'ASIR',
        'nombre' => 'ASIR',
        'descripcion' => 'Dudas que puedan tener a lo largo del curso',
        'icono' => '🖥️ '
    ],
    [
        'id' => 'sugerencias',
        'nombre' => 'Sugerencias',
        'descripcion' => 'Propuestas de mejora y nuevas ideas para el proyecto',
        'icono' => '💡'
    ],
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foros - Proyecto Antena Pringles</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .forums-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .forum-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .forum-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .forum-icon {
            font-size: 2.5rem;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 60px;
        }
        .forum-info {
            flex-grow: 1;
        }
        .forum-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2e7d32;
        }
        .forum-description {
            color: #555;
            font-size: 0.9rem;
        }
        .page-title {
            color: #2e7d32;
            margin-bottom: 30px;
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
    </style>
</head>
<body>
    <div class="forums-container">
        <div class="breadcrumb">
            <a href="principal.php">Inicio</a> &gt; Foros
        </div>
        
        <h1 class="page-title">Foros de Discusión</h1>
        
        <?php foreach ($foros as $foro): ?>
            <a href="foro_<?php echo $foro['id']; ?>.php" class="forum-card">
                <div class="forum-icon"><?php echo $foro['icono']; ?></div>
                <div class="forum-info">
                    <div class="forum-title"><?php echo htmlspecialchars($foro['nombre']); ?></div>
                    <div class="forum-description"><?php echo htmlspecialchars($foro['descripcion']); ?></div>
                </div>
            </a>
        <?php endforeach; ?>
        
        <div class="button-container">
            <a href="principal.php" class="back-button">Volver al menú principal</a>
        </div>
    </div>
</body>
</html>
