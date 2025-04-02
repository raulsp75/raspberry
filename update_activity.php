<?php
session_start();
require_once 'config/database.php';

// No mostrar resultados al usuario para que la solicitud AJAX funcione correctamente
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) {
        // Registrar error silenciosamente
        error_log("Error actualizando actividad del usuario: " . $e->getMessage());
    }
}

// Enviar respuesta correcta para AJAX
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?>
