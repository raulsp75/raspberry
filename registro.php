<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $termsAccepted = isset($_POST['terms']);
    
    if (!$termsAccepted) {
        echo "<script>alert('Debes aceptar los términos y condiciones');</script>";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        echo "<script>alert('La contraseña debe contener al menos una mayúscula, una minúscula y un número.');</script>";
    } elseif ($password !== $confirmPassword) {
        echo "<script>alert('Las contraseñas no coinciden');</script>";
    } else {
        echo "<script>alert('Usuario registrado con éxito'); window.location.href = 'login.php';</script>";
        // Aquí puedes añadir código para guardar el usuario en la base de datos
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
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                            <small class="form-text text-muted">Debe contener al menos una mayúscula, una minúscula y un número.</small>
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

