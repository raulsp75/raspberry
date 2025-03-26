<?php
declare(strict_types=1);
session_start();
unset($_SESSION['user']);
// Configuración de conexión a base de datos
$host = 'localhost';
$dbname = 'raspberry';
$username = 'root';
$password = '1473';
try {
    // Conexión a la base de datos con PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['usuario']) || !isset($_POST['clave'])) {
        $_SESSION['message'] = "Debe especificar usuario y contraseña";
        header("Location: login.php");
        exit();
    }

    $user = $_POST['usuario'];
    $password = $_POST['clave'];

    // Preparar consulta para buscar usuario - updated column names
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = :usuario");
    $stmt->execute(['usuario' => $user]);
    $userAccount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userAccount || !password_verify($password, $userAccount['contrasena'])) {
        $_SESSION['message'] = "Usuario o contraseña incorrectos";
        header("Location: login.php");
        exit();
    }
    
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $userAccount['id'];
    header("Location: principal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raspberry</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-96 text-center">
        <h1 class="text-3xl font-bold text-blue-600 mb-6">RaspberryPi</h1>
        <img src="i.png" alt="Raspberry Pi Logo" class="w-40 mx-auto mb-4">
        <?php if (isset($_SESSION['message'])): ?>
            <p class="text-red-500 mb-4"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-4 text-left">
                <label class="block text-gray-700 font-semibold mb-2" for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ingresa tu usuario">
            </div>
            <div class="mb-4 text-left">
                <label class="block text-gray-700 font-semibold mb-2" for="clave">Clave:</label>
                <input type="password" id="clave" name="clave" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ingresa tu clave">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition">Acceder</button>
            <div class="text-center mt-3">
                <a href="registro.php" class="text-blue-600 hover:underline">¿No tienes una cuenta? Regístrate</a>
            </div>
        </form>
    </div>
</body>
</html> 
