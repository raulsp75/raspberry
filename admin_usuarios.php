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

// Verificar si estamos en modo edición
$modo_edicion = isset($_GET['editar']) && !empty($_GET['editar']);
$user_id_editar = $modo_edicion ? $_GET['editar'] : null;

// --------- FUNCIONES PARA EDICIÓN DE USUARIO ---------
if ($modo_edicion && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $nuevo_password = trim($_POST['nuevo_password']);
    $rol = $_POST['rol'];
    $bloqueado = isset($_POST['bloqueado']) ? 1 : 0;
    
    // Validar datos
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    }
    
    if (empty($nombre_usuario)) {
        $errores[] = "El nombre de usuario es obligatorio.";
    }
    
    // Verificar si el nombre_usuario ya existe (excepto para el usuario actual)
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? AND id != ?");
    $stmt->execute([$nombre_usuario, $user_id_editar]);
    if ($stmt->rowCount() > 0) {
        $errores[] = "El nombre de usuario ya está en uso.";
    }
    
    // Si no hay errores, actualizar el usuario
    if (empty($errores)) {
        try {
            // Preparar la consulta base sin la contraseña
            $sql = "UPDATE usuarios SET nombre = ?, nombre_usuario = ?, rol = ?, bloqueado = ? WHERE id = ?";
            $params = [$nombre, $nombre_usuario, $rol, $bloqueado, $user_id_editar];
            
            // Si se proporciona una nueva contraseña, actualizar también la contraseña
            if (!empty($nuevo_password)) {
                $hash_password = password_hash($nuevo_password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre = ?, nombre_usuario = ?, contrasena = ?, rol = ?, bloqueado = ? WHERE id = ?";
                $params = [$nombre, $nombre_usuario, $hash_password, $rol, $bloqueado, $user_id_editar];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $mensaje = "Usuario actualizado correctamente.";
            $tipo_mensaje = "success";
            // Redirigir a la lista después de actualizar
            header("Location: admin_usuarios.php");
            exit();
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar el usuario: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "danger";
    }
}

// --------- FUNCIONES PARA LISTA DE USUARIOS ---------
// Procesar eliminación de usuario
if (!$modo_edicion && isset($_POST['eliminar_usuario']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $mensaje = "Usuario eliminado correctamente.";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el usuario: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Procesar bloqueo/desbloqueo de usuario
if (!$modo_edicion && isset($_POST['toggle_bloqueo']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET bloqueado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $user_id]);
        $mensaje = $nuevo_estado == 1 ? "Usuario bloqueado correctamente." : "Usuario desbloqueado correctamente.";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al cambiar el estado del usuario: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Procesar cambio de rol
if (!$modo_edicion && isset($_POST['cambiar_rol']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $nuevo_rol = $_POST['nuevo_rol'];
    
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        $stmt->execute([$nuevo_rol, $user_id]);
        $mensaje = "Rol de usuario actualizado correctamente.";
        $tipo_mensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al cambiar el rol del usuario: " . $e->getMessage();
        $tipo_mensaje = "danger";
    }
}

// Obtener datos según el modo
if ($modo_edicion) {
    // Obtener los datos del usuario a editar
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id_editar]);
        $usuario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario_editar) {
            header("Location: admin_usuarios.php");
            exit();
        }
    } catch (PDOException $e) {
        $mensaje = "Error al obtener los datos del usuario: " . $e->getMessage();
        $tipo_mensaje = "danger";
        header("Location: admin_usuarios.php");
        exit();
    }
} else {
    // Obtener lista de usuarios para el modo de lista
    try {
        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY nombre_usuario");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $mensaje = "Error al obtener la lista de usuarios: " . $e->getMessage();
        $tipo_mensaje = "danger";
        $usuarios = [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modo_edicion ? 'Editar Usuario' : 'Gestión de Usuarios'; ?> - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .user-table th, .user-table td {
            vertical-align: middle;
        }
        .admin-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .search-box {
            border-radius: 20px;
            padding-left: 40px;
            position: relative;
        }
        .search-icon {
            position: absolute;
            left: 15px;
            top: 10px;
            color: #6c757d;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-label {
            font-weight: 500;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 10px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Encabezado de la página -->
        <div class="admin-header text-center">
            <?php if ($modo_edicion): ?>
                <h1 class="display-5 mb-3">Editar Usuario</h1>
                <p class="lead">Modifica los datos del usuario: <strong><?php echo htmlspecialchars($usuario_editar['nombre_usuario']); ?></strong></p>
            <?php else: ?>
                <h1 class="display-5 mb-3">Gestión de Usuarios</h1>
                <p class="lead">Administra los usuarios registrados en el sistema.</p>
            <?php endif; ?>
        </div>

        <!-- Mensajes de alerta -->
        <?php if (isset($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if ($modo_edicion): ?>
            <!-- FORMULARIO DE EDICIÓN DE USUARIO -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="form-container">
                        <form method="post" action="admin_usuarios.php?editar=<?php echo $user_id_editar; ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre completo</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario_editar['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombre_usuario" class="form-label">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($usuario_editar['nombre_usuario']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nuevo_password" class="form-label">Nueva contraseña (dejar en blanco para mantener)</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control" id="nuevo_password" name="nuevo_password">
                                        <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                                    </div>
                                    <small class="text-muted">Si no deseas cambiar la contraseña, deja este campo en blanco.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="rol" class="form-label">Rol de usuario</label>
                                    <select class="form-select" id="rol" name="rol" <?php echo $usuario_editar['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                        <option value="usuario" <?php echo $usuario_editar['rol'] === 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                                        <option value="admin" <?php echo $usuario_editar['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                    <?php if ($usuario_editar['id'] == $_SESSION['user_id']): ?>
                                    <small class="text-muted">No puedes cambiar tu propio rol.</small>
                                    <input type="hidden" name="rol" value="<?php echo $usuario_editar['rol']; ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="bloqueado" name="bloqueado" value="1" <?php echo $usuario_editar['bloqueado'] ? 'checked' : ''; ?> <?php echo $usuario_editar['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                    <label class="form-check-label" for="bloqueado">Usuario bloqueado</label>
                                </div>
                                <?php if ($usuario_editar['id'] == $_SESSION['user_id']): ?>
                                <small class="text-muted">No puedes bloquearte a ti mismo.</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="admin_usuarios.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver a la lista
                                </a>
                                <button type="submit" name="actualizar_usuario" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Información adicional y de auditoria -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Información de auditoria</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted">ID de usuario</p>
                            <p class="fw-bold"><?php echo htmlspecialchars($usuario_editar['id']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted">Fecha de registro</p>
                            <p class="fw-bold"><?php echo isset($usuario_editar['fecha_registro']) ? htmlspecialchars($usuario_editar['fecha_registro']) : 'No disponible'; ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted">Último acceso</p>
                            <p class="fw-bold"><?php echo isset($usuario_editar['last_activity']) ? htmlspecialchars($usuario_editar['last_activity']) : 'Nunca'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        
        <?php else: ?>
            <!-- LISTA DE USUARIOS -->
            
            <!-- Barra de búsqueda y filtros -->
            <div class="row mb-4">
                <div class="col-md-6 position-relative">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchUser" class="form-control search-box" placeholder="Buscar usuario...">
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">Todos</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="admin">Administradores</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="usuario">Usuarios</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="bloqueado">Bloqueados</button>
                    </div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover user-table" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Nombre</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Último acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr class="user-row" 
                                    data-role="<?php echo htmlspecialchars($usuario['rol']); ?>"
                                    data-blocked="<?php echo $usuario['bloqueado'] ? '1' : '0'; ?>">
                                    <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $usuario['rol'] === 'admin' ? 'bg-danger' : 'bg-info'; ?>">
                                            <?php echo htmlspecialchars($usuario['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $usuario['bloqueado'] ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo $usuario['bloqueado'] ? 'Bloqueado' : 'Activo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $usuario['last_activity'] ? htmlspecialchars($usuario['last_activity']) : 'Nunca'; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <!-- Botón para editar usuario -->
                                            <a href="admin_usuarios.php?editar=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <!-- Botón para bloquear/desbloquear usuario -->
                                            <form method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de <?php echo $usuario['bloqueado'] ? 'desbloquear' : 'bloquear'; ?> a este usuario?');">
                                                <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?php echo $usuario['bloqueado'] ? '0' : '1'; ?>">
                                                <button type="submit" name="toggle_bloqueo" class="btn btn-sm <?php echo $usuario['bloqueado'] ? 'btn-outline-success' : 'btn-outline-warning'; ?>">
                                                    <i class="bi <?php echo $usuario['bloqueado'] ? 'bi-unlock' : 'bi-lock'; ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <!-- Botón para cambiar rol (solo si no es el propio usuario administrador) -->
                                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de cambiar el rol de este usuario?');">
                                                <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                                <input type="hidden" name="nuevo_rol" value="<?php echo $usuario['rol'] === 'admin' ? 'usuario' : 'admin'; ?>">
                                                <button type="submit" name="cambiar_rol" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-person-gear"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            
                                            <!-- Botón para eliminar usuario (solo si no es el propio usuario administrador) -->
                                            <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                                                <button type="submit" name="eliminar_usuario" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($usuarios)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No hay usuarios registrados en el sistema.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Botones de navegación -->
        <div class="text-center mt-5">
            <?php if ($modo_edicion): ?>
                <a href="admin_usuarios.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Volver a la lista de usuarios
                </a>
            <?php else: ?>
                <a href="admin.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Volver al Panel de Administración
                </a>
            <?php endif; ?>
            <a href="principal.php" class="btn btn-outline-primary" onclick="sessionStorage.removeItem('currentSection');">
                <i class="bi bi-house"></i> Ir al Menú Principal
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (!$modo_edicion): ?>
            // Búsqueda de usuarios
            $("#searchUser").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#usersTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // Filtrado de usuarios
            $(".filter-btn").click(function() {
                $(".filter-btn").removeClass("active");
                $(this).addClass("active");
                
                var filter = $(this).data("filter");
                
                if (filter === "all") {
                    $(".user-row").show();
                } else if (filter === "admin") {
                    $(".user-row").hide();
                    $(".user-row[data-role='admin']").show();
                } else if (filter === "usuario") {
                    $(".user-row").hide();
                    $(".user-row[data-role='usuario']").show();
                } else if (filter === "bloqueado") {
                    $(".user-row").hide();
                    $(".user-row[data-blocked='1']").show();
                }
            });
            <?php endif; ?>
            
            <?php if ($modo_edicion): ?>
            // Toggle para mostrar/ocultar contraseña
            $("#togglePassword").click(function() {
                const passwordInput = document.getElementById('nuevo_password');
                const icon = this;
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
