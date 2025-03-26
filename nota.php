<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Definir asignaturas por curso
$asignaturas_primero = [
    'IDP' => 'Implantación de Sistemas Operativos',
    'PAR' => 'Planificación y Administración de Redes',
    'FUM' => 'Fundamentos de Hardware',
    'GBD' => 'Gestión de Bases de Datos',
    'LND' => 'Lenguajes de Marcas'
];

$asignaturas_segundo = [
    'ADD' => 'Administración de Sistemas Operativos',
    'SRD' => 'Servicios de Red e Internet',
    'IAW' => 'Implantación de Aplicaciones Web',
    'ADE' => 'Administración de Sistemas Gestores de Bases de Datos',
    'SGY' => 'Seguridad y Alta Disponibilidad'
];

// Obtener el curso desde la solicitud POST
$curso = $_POST['curso'] ?? null;

// Verificar si el curso es válido
if (!in_array($curso, ['primero', 'segundo'])) {
    echo "<div class='alert alert-danger'>Curso no válido</div>";
    exit();
}

// Seleccionar el conjunto de asignaturas correcto
$asignaturas = ($curso === 'primero') ? $asignaturas_primero : $asignaturas_segundo;

// Archivo para guardar las notas (puedes usar una base de datos real en un futuro)
$archivo_notas = "notas_" . $curso . ".txt";

// Crear archivo de notas si no existe
if (!file_exists($archivo_notas)) {
    file_put_contents($archivo_notas, "");
}

// Leer notas existentes
$notas = [];
$lineas = file($archivo_notas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lineas as $linea) {
    $datos = explode('|', $linea);
    if (count($datos) === 2) {
        $notas[$datos[0]] = $datos[1];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="text-center">Notas de <?php echo ucfirst($curso); ?> Curso</h3>
        </div>
        <div class="card-body">
            <?php if (empty($notas)): ?>
                <div class="alert alert-info">
                    No hay notas registradas para este curso.
                </div>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Asignatura</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asignaturas as $codigo => $nombre): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nombre); ?></td>
                                <td>
                                    <?php 
                                    echo isset($notas[$codigo]) 
                                        ? htmlspecialchars($notas[$codigo]) 
                                        : '<span class="text-muted">Sin nota</span>'; 
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
