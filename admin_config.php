<?php 
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: principal.php");
    exit();
}

// Incluir la configuración de la base de datos
require_once 'config/database.php';

$mensaje = $tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['reiniciarRaspberry'])) {
       $mensaje = "Iniciando reinicio del sistema...";
        $tipo_mensaje = "warning";
        shell_exec("sudo reboot");
    }
    
    if (isset($_POST['limpiarCache'])) {
        shell_exec("sudo rm -rf /tmp/cache/*");
        $mensaje = "Caché del sistema limpiada correctamente.";
        $tipo_mensaje = "success";
    }
    
    if (isset($_POST['backupDB'])) {
        if (!file_exists('backups')) {
            mkdir('backups', 0755, true);
        }
        
        $fecha = date('Y-m-d_H-i-s');
        $nombre_backup = "backups/backup_db_$fecha.sql";
        $return_var = 0;
        try {
            $comando = "/usr/bin/mysqldump -u root -p'1473' raspberry > $nombre_backup 2>&1";
            $output = shell_exec($comando);
            echo "<pre>$output</pre>";
            if ($return_var === 0) {
                $mensaje = "Copia de seguridad de la base de datos creada correctamente: " . basename($nombre_backup);
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al crear la copia de seguridad de la base de datos.";
                $tipo_mensaje = "danger";
            }
        } catch (Exception $e) {
            $mensaje = "Error al realizar la copia de seguridad: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
    
    if (isset($_POST['updateSystem'])) {
        shell_exec("sudo apt update && sudo apt upgrade -y");
        $mensaje = "Sistema actualizado correctamente.";
        $tipo_mensaje = "success";
    }
}

$hostname = shell_exec("hostname");
$uptime = shell_exec("uptime");
$disk_space = shell_exec("df -h | grep /dev/root");
$system_version = shell_exec("cat /etc/os-release | grep PRETTY_NAME");
$kernel_version = shell_exec("uname -r");
$memory_stats = shell_exec("free -h");
$load_average = shell_exec("uptime | awk -F'load average:' '{print $2}'");
$active_users = shell_exec("who | wc -l");
$processes = shell_exec("ps aux | wc -l");

$backups = [];
if (file_exists('backups')) {
    $files = scandir('backups');
    foreach ($files as $file) {
        if (strpos($file, 'backup_db_') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = $file;
        }
    }
    rsort($backups);
    $backups = array_slice($backups, 0, 5);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración General - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .config-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
            margin-bottom: 20px;
        }
        .config-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .config-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #6c757d;
        }
        .system-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .system-info-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px dashed #e9ecef;
            padding-bottom: 10px;
        }
        .system-info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .system-info-label {
            font-weight: bold;
            width: 150px;
            color: #495057;
        }
        .system-info-value {
            flex: 1;
            font-family: 'Courier New', monospace;
            word-break: break-word;
        }
        .restart-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .restart-btn:hover {
            background-color: #c82333;
        }
        .backup-btn {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .backup-btn:hover {
            background-color: #0a58ca;
        }
        .update-btn {
            background-color: #198754;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .update-btn:hover {
            background-color: #157347;
        }
        .clean-btn {
            background-color: #ffc107;
            color: black;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .clean-btn:hover {
            background-color: #e0a800;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 2px solid #0d6efd;
            color: #0d6efd;
            font-weight: bold;
        }
        .tab-content {
            padding-top: 20px;
        }
        .backup-list {
            margin-top: 15px;
            max-height: 200px;
            overflow-y: auto;
        }
        .backup-item {
            padding: 8px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .backup-item:last-child {
            border-bottom: none;
        }
        .backup-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .download-btn {
            font-size: 0.85rem;
            padding: 2px 8px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Encabezado de la página -->
        <div class="admin-header text-center">
            <h1 class="display-5 mb-3">Configuración General</h1>
            <p class="lead">Gestiona los parámetros y ajustes del sistema.</p>
        </div>

        <!-- Mensajes de alerta -->
        <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Pestañas de navegación -->
        <ul class="nav nav-tabs mb-4" id="configTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#infoSystem" type="button" role="tab" aria-controls="infoSystem" aria-selected="true">
                    <i class="bi bi-info-circle me-1"></i> Información del Sistema
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="action-tab" data-bs-toggle="tab" data-bs-target="#actionSystem" type="button" role="tab" aria-controls="actionSystem" aria-selected="false">
                    <i class="bi bi-gear me-1"></i> Acciones
                </button>
            </li>
        </ul>

        <div class="tab-content" id="configTabContent">
            <!-- Pestaña de Información del Sistema -->
            <div class="tab-pane fade show active" id="infoSystem" role="tabpanel" aria-labelledby="info-tab">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h4 class="mb-0">Información del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <div class="system-info">
                            <div class="system-info-row">
                                <div class="system-info-label">Hostname:</div>
                                <div class="system-info-value"><?php echo trim($hostname); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Uptime:</div>
                                <div class="system-info-value"><?php echo trim($uptime); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Carga del sistema:</div>
                                <div class="system-info-value"><?php echo trim($load_average); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Versión del sistema:</div>
                                <div class="system-info-value"><?php echo str_replace(['PRETTY_NAME=', '"'], '', trim($system_version)); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Versión del kernel:</div>
                                <div class="system-info-value"><?php echo trim($kernel_version); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Espacio en disco:</div>
                                <div class="system-info-value"><?php echo trim($disk_space); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Usuarios activos:</div>
                                <div class="system-info-value"><?php echo trim($active_users); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Procesos activos:</div>
                                <div class="system-info-value"><?php echo trim($processes); ?></div>
                            </div>
                            
                            <div class="system-info-row">
                                <div class="system-info-label">Memoria:</div>
                                <div class="system-info-value">
                                    <pre><?php echo trim($memory_stats); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pestaña de Acciones -->
            <div class="tab-pane fade" id="actionSystem" role="tabpanel" aria-labelledby="action-tab">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card config-card">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="config-icon">
                                        <i class="bi bi-power"></i>
                                    </div>
                                    <h5 class="card-title">Reiniciar Sistema</h5>
                                </div>
                                <p class="card-text">Reinicia el sistema completo. El servicio se interrumpirá temporalmente hasta que el servidor vuelva a estar en línea.</p>
                                <form method="post" action="" onsubmit="return confirm('¿Estás seguro de que deseas reiniciar el sistema?');">
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="reiniciarRaspberry" class="restart-btn">
                                            <i class="bi bi-power"></i> Reiniciar Sistema
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card config-card">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="config-icon">
                                        <i class="bi bi-database-check"></i>
                                    </div>
                                    <h5 class="card-title">Copia de Seguridad</h5>
                                </div>
                                <p class="card-text">Realiza una copia de seguridad de la base de datos y guárdala en la carpeta de backups del servidor.</p>
                                <form method="post" action="" onsubmit="return confirm('¿Estás seguro de que deseas crear una copia de seguridad?');">
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="backupDB" class="backup-btn">
                                            <i class="bi bi-database-down"></i> Crear Backup
                                        </button>
                                    </div>
                                </form>
                                
                                <?php if (!empty($backups)): ?>
                                <div class="mt-3">
                                    <h6>Copias de seguridad recientes:</h6>
                                    <div class="backup-list list-group">
                                        <?php foreach ($backups as $backup): ?>
                                            <div class="backup-item">
                                                <div>
                                                    <i class="bi bi-file-earmark-text"></i> 
                                                    <?php echo $backup; ?>
                                                    <div class="backup-date">
                                                        <?php 
                                                            $timestamp = filemtime('backups/' . $backup);
                                                            echo date('d/m/Y H:i', $timestamp); 
                                                        ?>
                                                    </div>
                                                </div>
                                                <a href="backups/<?php echo $backup; ?>" download class="btn btn-sm btn-outline-primary download-btn">Descargar</a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card config-card">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="config-icon">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </div>
                                    <h5 class="card-title">Actualizar Sistema</h5>
                                </div>
                                <p class="card-text">Actualiza el sistema operativo y paquetes del servidor a las últimas versiones disponibles.</p>
                                <form method="post" action="" onsubmit="return confirm('¿Estás seguro de que deseas actualizar el sistema?');">
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="updateSystem" class="update-btn">
                                            <i class="bi bi-arrow-repeat"></i> Actualizar Sistema
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card config-card">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="config-icon">
                                        <i class="bi bi-speedometer"></i>
                                    </div>
                                    <h5 class="card-title">Limpiar Caché</h5>
                                </div>
                                <p class="card-text">Limpia los archivos temporales y la caché del sistema para liberar espacio y mejorar el rendimiento.</p>
                                <form method="post" action="" onsubmit="return confirm('¿Estás seguro de que deseas limpiar la caché del sistema?');">
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="limpiarCache" class="clean-btn">
                                            <i class="bi bi-trash"></i> Limpiar Caché
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
</body>
</html>
