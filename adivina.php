<?php
session_start();

// Procesamiento Ajax
if (isset($_POST['ajax_action'])) {
    $response = ['success' => true];
    
    // Inicializar juego
    if ($_POST['ajax_action'] == 'iniciar') {
        $_SESSION['numero_secreto'] = rand(1, 100);
        $_SESSION['intentos'] = 0;
        $_SESSION['adivinado'] = false;
        $_SESSION['mensaje'] = '';
        $response['intentos'] = 0;
        $response['mensaje'] = '';
    }
    
    // Procesar intento
    if ($_POST['ajax_action'] == 'adivinar' && isset($_POST['intento'])) {
        if (!isset($_SESSION['numero_secreto'])) {
            $_SESSION['numero_secreto'] = rand(1, 100);
            $_SESSION['intentos'] = 0;
            $_SESSION['adivinado'] = false;
        }
        
        $_SESSION['intentos']++;
        $intento = intval($_POST['intento']);
        $response['intentos'] = $_SESSION['intentos'];
        
        if ($intento == $_SESSION['numero_secreto']) {
            $_SESSION['adivinado'] = true;
            $response['mensaje'] = "<div class='alert alert-success'>¡Felicidades! Has adivinado el número en {$_SESSION['intentos']} intentos.</div>";
            $response['adivinado'] = true;
        } elseif ($intento < $_SESSION['numero_secreto']) {
            $response['mensaje'] = "<div class='alert alert-info'>El número es mayor que {$intento}. Intento #{$_SESSION['intentos']}</div>";
        } else {
            $response['mensaje'] = "<div class='alert alert-info'>El número es menor que {$intento}. Intento #{$_SESSION['intentos']}</div>";
        }
    }
    
    echo json_encode($response);
    exit;
}

// Inicializar el juego para la primera carga
if (!isset($_SESSION['numero_secreto'])) {
    $_SESSION['numero_secreto'] = rand(1, 100);
    $_SESSION['intentos'] = 0;
    $_SESSION['adivinado'] = false;
    $_SESSION['mensaje'] = '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego Adivina el Número</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-0">¡Adivina el número!</h3>
                </div>
                <div class="card-body text-center p-4">
                    <p class="lead mb-4">Adivina un número entre 1 y 100.</p>
                    
                    <div id="mensaje-resultado">
                        <?php if (isset($_SESSION['mensaje']) && $_SESSION['mensaje']): ?>
                            <?php echo $_SESSION['mensaje']; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Contenedor para el video de celebración (oculto inicialmente) -->
                    <div id="video" class="mt-3 mb-4" style="display: none;">
                        <div class="ratio ratio-16x9">
                            <video id="video-exito" controls>
                                <source src="video.mp4" type="video/mp4">
                                Tu navegador no soporta videos HTML5.
                            </video>
                        </div>
                    </div>
                    
                    <div id="form-adivinar" <?php echo ($_SESSION['adivinado']) ? 'style="display:none;"' : ''; ?>>
                        <div class="input-group mb-3 justify-content-center">
                            <input type="number" id="numero-intento" min="1" max="100" class="form-control text-center" style="max-width: 150px;" placeholder="Tu número" required>
                            <button type="button" id="btn-adivinar" class="btn btn-primary px-4">Adivinar</button>
                        </div>
                    </div>
                    
                    <button type="button" id="btn-reiniciar" class="btn btn-secondary mt-3">Reiniciar juego</button>
                </div>
                
                <div class="card-footer bg-light text-center py-2">
                    <small class="text-muted">Intentos realizados: <span id="contador-intentos"><?php echo $_SESSION['intentos']; ?></span></small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Función para adivinar
    $("#btn-adivinar").click(function() {
        let intento = $("#numero-intento").val();
        if (intento >= 1 && intento <= 100) {
            $.ajax({
                url: "adivina.php", // Apunta al mismo archivo
                method: "POST",
                data: { 
                    ajax_action: "adivinar",
                    intento: intento 
                },
                success: function(response) {
                    let data = JSON.parse(response);
                    $("#mensaje-resultado").html(data.mensaje);
                    $("#contador-intentos").text(data.intentos);
                    
                    // Si adivinó, ocultar el formulario y mostrar video
                    if (data.adivinado) {
                        $("#form-adivinar").hide();
                        
                        // Mostrar y reproducir el video de celebración
                        $("#video").show();
                        var video = document.getElementById("video-exito");
                        video.play();
                    }
                }
            });
        }
    });
    
    // Reiniciar juego
    $("#btn-reiniciar").click(function() {
        $.ajax({
            url: "adivina.php", // Apunta al mismo archivo
            method: "POST",
            data: { ajax_action: "iniciar" },
            success: function(response) {
                let data = JSON.parse(response);
                $("#mensaje-resultado").html("");
                $("#contador-intentos").text("0");
                $("#numero-intento").val("");
                $("#form-adivinar").show();
                
                // Ocultar el video
                $("#video").hide();
                var video = document.getElementById("video-exito");
                video.pause();
                video.currentTime = 0;
            }
        });
    });

    // También permitir envío con Enter
    $("#numero-intento").keypress(function(e) {
        if (e.which == 13) {
            $("#btn-adivinar").click();
            return false;
        }
    });
});
</script>

</body>
</html>

