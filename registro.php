<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$db_username = "root"; // Database username
$db_password = "1473"; // Database password
$dbname = "raspberry";

// Habilitar reportes de error para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión para manejar mensajes
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar y validar entradas de manera moderna
    $name = trim(strip_tags($_POST['name'] ?? ''));
    $username = trim(strip_tags($_POST['username'] ?? ''));
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $termsAccepted = isset($_POST['terms']);
    
    // Arreglo para almacenar errores
    $errors = [];
    
    // Validaciones más detalladas
    if (empty($name)) {
        $errors[] = "El nombre es obligatorio";
    }

    if (empty($username)) {
        $errors[] = "El nombre de usuario es obligatorio";
    }

    if (!$termsAccepted) {
        $errors[] = "Debes aceptar los términos y condiciones";
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $errors[] = "La contraseña debe contener al menos una mayúscula, una minúscula, un número y tener al menos 8 caracteres";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Las contraseñas no coinciden";
    }

    // Si no hay errores, proceder con el registro
    if (empty($errors)) {
        try {
            // Crear conexión - use fixed database credentials
            $conn = new mysqli($servername, $db_username, $db_password, $dbname);
            
            // Verificar conexión
            if ($conn->connect_error) {
                throw new Exception("Conexión fallida: " . $conn->connect_error);
            }
            
            // Verificar si el nombre de usuario ya existe
            $check_stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = "El nombre de usuario ya está en uso";
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Hash de la contraseña para mayor seguridad
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Preparar la consulta SQL para insertar el usuario
                $sql = "INSERT INTO usuarios (nombre, nombre_usuario, contrasena) VALUES (?, ?, ?)";
                
                // Preparar y vincular parámetros
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $name, $username, $hashed_password);
                
                // Ejecutar la consulta
                if ($stmt->execute()) {
                    // Registro exitoso
                    $_SESSION['success_message'] = "Usuario registrado con éxito";
                    header("Location: login.php");
                    exit();
                } else {
                    $errors[] = "Error al registrar el usuario: " . $stmt->error;
                }
                
                // Cerrar statement
                $stmt->close();
            }
            
            // Cerrar conexión
            $conn->close();
            
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Si hay errores, guardarlos en la sesión
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h3 class="text-center">Registro de Usuario</h3>
                    
                    <?php
                    // Mostrar errores de registro
                    if (isset($_SESSION['registration_errors'])) {
                        echo '<div class="alert alert-danger">';
                        foreach ($_SESSION['registration_errors'] as $error) {
                            echo htmlspecialchars($error) . '<br>';
                        }
                        echo '</div>';
                        // Limpiar los errores de la sesión
                        unset($_SESSION['registration_errors']);
                    }
                    ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                            <small class="form-text text-muted">Debe contener al menos una mayúscula, una minúscula, un número y tener al menos 8 caracteres.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" name="confirmPassword" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="terms" required>
                            <label class="form-check-label" for="terms">Acepto los <a href="#">términos y condiciones</a></label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                        <div class="text-center mt-3">
                            <a href="login.php">¿Ya tienes una cuenta? Inicia sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
