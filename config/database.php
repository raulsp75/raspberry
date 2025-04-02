<?php
// Evitar que se muestre directamente
if (!defined('SECURE_ACCESS') && basename($_SERVER['PHP_SELF']) == 'database.php') {
    exit('Acceso directo no permitido');
}

// Configuración de la base de datos
$host = 'localhost';      // Host de la base de datos (generalmente localhost)
$dbname = 'raspberry';    // Nombre de tu base de datos (parece ser 'raspberry' según tu captura)
$user = 'root';           // Usuario de la base de datos
$password = '1473';           // Contraseña (si tienes una)

try {
    // Establecer la conexión usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Opcional: configurar el modo de recuperación por defecto
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // En producción, es mejor registrar el error y mostrar un mensaje genérico
    // error_log('Error de conexión: ' . $e->getMessage());
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
?>
