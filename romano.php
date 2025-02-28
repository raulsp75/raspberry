<?php
session_start();

// Función para convertir números a romanos
function convertirARomano($numero) {
    $valores = [
        1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 
        100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 
        10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'
    ];
    
    $resultado = "";
    
    foreach ($valores as $valor => $romano) {
        while ($numero >= $valor) {
            $resultado .= $romano;
            $numero -= $valor;
        }
    }
    
    return $resultado;
}

// Procesamiento de AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero'])) {
    header('Content-Type: application/json'); // Asegurar respuesta JSON

    $numero = intval($_POST['numero']);

    if ($numero >= 1 && $numero <= 1000) {
        $romano = convertirARomano($numero);
        echo json_encode(['exito' => true, 'romano' => $romano]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => "Número fuera de rango (1-1000)"]);
    }
    exit;
}
?>

<!-- INTERFAZ HTML -->
<div class="card shadow p-4">
    <h3 class="text-center text-primary">Conversor de Números a Romanos</h3>
    <p class="text-center">Introduce un número entre 1 y 1000 para obtener su equivalente en números romanos.</p>

    <div class="input-group mb-3 justify-content-center">
       <input type="number" id="numero-romano" min="1" max="1000" class="form-control text-center" style="max-width: 200px;" placeholder="Introduce un número">
       <button id="btn-convertir" class="btn btn-success">Convertir</button>
    </div>

    <div id="resultado-romano"></div>
</div>

<!-- SCRIPT PARA AJAX -->
<script>
$(document).ready(function() {
    $("#btn-convertir").click(function() {
        let numero = $("#numero-romano").val();

        if (numero >= 1 && numero <= 1000) {
            $.ajax({
                url: "romano.php", // Archivo PHP que procesa la conversión
                method: "POST",
                data: { numero: numero },
                dataType: "json",
                success: function(response) {
                    console.log("Respuesta recibida:", response); // Debugging en consola

                    if (response.exito) {
                        $("#resultado-romano").html("<div class='alert alert-success'>Número romano: <strong>" + response.romano + "</strong></div>");
                    } else {
                        $("#resultado-romano").html("<div class='alert alert-danger'>" + response.mensaje + "</div>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error en la solicitud AJAX:", error);
                    $("#resultado-romano").html("<div class='alert alert-danger'>Error al procesar la conversión.</div>");
                }
            });
        } else {
            $("#resultado-romano").html("<div class='alert alert-danger'>Número fuera de rango (1-1000).</div>");
        }
    });
});
</script>
