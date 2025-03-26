<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Datos del examen con 10 preguntas sobre Implantación de Sistemas Operativos
$preguntas = [
    [
        'pregunta' => '¿Qué comando se utiliza para mostrar los procesos en ejecución en Linux?',
        'opciones' => ['ls', 'ps', 'top', 'cat'],
        'correcta' => 1
    ],
    // ... (resto de las preguntas como antes)
];

// Procesar examen
$resultado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $puntuacion = 0;
    $total = count($preguntas);
    
    foreach ($preguntas as $i => $pregunta) {
        $respuesta = $_POST["respuesta_$i"] ?? null;
        
        if ($respuesta !== null && (int)$respuesta === $pregunta['correcta']) {
            $puntuacion++;
        }
    }
    
    // Calcular resultado
    $resultado = [
        'puntuacion' => $puntuacion,
        'total' => $total,
        'porcentaje' => round(($puntuacion / $total) * 100, 2)
    ];
    
    // Guardar nota en archivo de texto
    $archivo_notas = "notas_primero.txt";
    
    // Leer notas existentes
    $notas = [];
    if (file_exists($archivo_notas)) {
        $lineas = file($archivo_notas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            $datos = explode('|', $linea);
            if (count($datos) === 2) {
                $notas[$datos[0]] = $datos[1];
            }
        }
    }
    
    // Añadir o actualizar nota de IDP
    $notas['IDP'] = $resultado['porcentaje'];
    
    // Guardar notas actualizadas
    $contenido_notas = '';
    foreach ($notas as $asignatura => $nota) {
        $contenido_notas .= "$asignatura|$nota\n";
    }
    file_put_contents($archivo_notas, $contenido_notas);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Examen de Implantación de Sistemas Operativos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <?php if ($resultado === null): ?>
        <!-- Formulario de examen -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center">Examen de Implantación de Sistemas Operativos</h2>
            </div>
            <div class="card-body">
                <form method="post">
                    <?php foreach ($preguntas as $i => $pregunta): ?>
                        <div class="mb-4">
                            <h5><?php echo htmlspecialchars($pregunta['pregunta']); ?></h5>
                            <?php foreach ($pregunta['opciones'] as $j => $opcion): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="respuesta_<?php echo $i; ?>" 
                                           id="pregunta_<?php echo $i; ?>_<?php echo $j; ?>" 
                                           value="<?php echo $j; ?>" 
                                           required>
                                    <label class="form-check-label" for="pregunta_<?php echo $i; ?>_<?php echo $j; ?>">
                                        <?php echo htmlspecialchars($opcion); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Terminar Examen</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Resultados del examen -->
        <div class="card">
            <div class="card-header bg-<?php 
                echo $resultado['porcentaje'] >= 50 ? 'success' : 'danger'; 
                ?> text-white">
                <h2 class="text-center">Resultados del Examen</h2>
            </div>
            <div class="card-body text-center">
                <h3>Puntuación: <?php echo $resultado['puntuacion']; ?>/<?php echo $resultado['total']; ?></h3>
                <h4>Porcentaje: <?php echo $resultado['porcentaje']; ?>%</h4>
                
                <?php if ($resultado['porcentaje'] == 100): ?>
                    <div class="alert alert-success">
                        🏆 ¡Puntuación perfecta! Felicidades
                    </div>
                <?php elseif ($resultado['porcentaje'] >= 50): ?>
                    <div class="alert alert-info">
                        👍 ¡Aprobaste el examen! Buen trabajo
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        📚 Necesitas repasar el material
                    </div>
                <?php endif; ?>
                
                <a href="principal.php" class="btn btn-primary">Volver al Menú Principal</a>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
