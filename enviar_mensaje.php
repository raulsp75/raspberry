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

// Verificar si hay un mensaje para procesar
if (!isset($_POST['message']) || empty($_POST['message'])) {
    // Si no hay mensaje, redirigir al foro
    header('Location: foro.php');
    exit;
}

// Mensaje de depuración (opcional, puedes comentarlo o eliminarlo)
echo "Procesando mensaje...<br>";
echo "ID de usuario: " . $_SESSION['user_id'] . "<br>";
echo "Mensaje: " . htmlspecialchars($_POST['message']) . "<br>";

try {
    // Obtener información del usuario desde la base de datos
    $stmt = $pdo->prepare("SELECT nombre_usuario FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        throw new Exception("No se pudo encontrar el usuario con ID: " . $_SESSION['user_id']);
    }
    
    $username = $usuario['nombre_usuario'];
    $message = htmlspecialchars($_POST['message']);
    $user_id = $_SESSION['user_id'];
    
    echo "Nombre de usuario: " . $username . "<br>";
    
    // Insertar el mensaje en la base de datos
    $stmt = $pdo->prepare("INSERT INTO forum_messages (user_id, username, message) VALUES (?, ?, ?)");
    $result = $stmt->execute([$user_id, $username, $message]);
    
    if (!$result) {
        throw new Exception("Error al insertar en la base de datos: " . implode(" ", $stmt->errorInfo()));
    }
    
    echo "Mensaje guardado correctamente. Redirigiendo al foro...";
    
    // Esperar 2 segundos para que el usuario pueda ver el mensaje de éxito
    sleep(2);
    
    // Redirigir de vuelta al foro
    header('Location: foro.php');
    exit;
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; background-color: #ffebee; margin: 10px 0;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
    echo "<p><a href='foro.php'>Volver al foro</a></p>";
}
?>
