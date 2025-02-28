<?php declare(strict_types=1);

session_start();
unset($_SESSION['user']);

$userAccount = [
    'admin' => password_hash('1234', PASSWORD_DEFAULT)
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['usuario']) || !isset($_POST['clave'])) {
        $_SESSION['message'] = "Debe especificar usuario y contraseña";
        header("Location: index.php");
        exit();
    }

    $user = $_POST['usuario'];
    $password = $_POST['clave'];

    if (!isset($userAccount[$user]) || !password_verify($password, $userAccount[$user])) {
        $_SESSION['message'] = "Usuario o contraseña incorrectos";
        header("Location: login.php");
        exit();
    }

    $_SESSION['user'] = $user;
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
            <p class="text-red-500 mb-4"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
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
        </form>
    </div>
</body>
</html>

