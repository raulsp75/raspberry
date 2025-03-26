<?php
session_start();

// Manejar el cambio de color de fondo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['background_color'])) {
    // Sanitizar el color
    $color = htmlspecialchars($_POST['background_color']);
    
    // Establecer la cookie con el color de fondo
    setcookie('background_color', $color, time() + (86400 * 30), "/"); // 30 días de duración
    
    // Mensaje de éxito
    echo '<div class="alert alert-success">Color de fondo actualizado correctamente</div>';
    echo '<script>
            setTimeout(function() {
                $("body").css("background-color", "' . $color . '");
            }, 300);
          </script>';
    exit();
}

// Obtener el color actual de la cookie
$current_color = isset($_COOKIE['background_color']) ? $_COOKIE['background_color'] : '#f8f9fa';
?>

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuración del Color de Fondo</h5>
        </div>
        <div class="card-body">
            <div id="responseMessage"></div>
            
            <form id="colorForm" method="post">
                <div class="mb-3">
                    <label for="background_color" class="form-label">Selecciona un color de fondo:</label>
                    <input type="color" class="form-control form-control-color" id="background_color" name="background_color" value="<?php echo $current_color; ?>" title="Elige un color para el fondo">
                    <div class="form-text">El color seleccionado se aplicará al fondo de tu panel.</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Vista previa:</label>
                    <div id="colorPreview" class="p-4 rounded border" style="background-color: <?php echo $current_color; ?>;">
                        <p class="text-center mb-0">Este será el aspecto de tu fondo</p>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Actualizar vista previa cuando se cambia el color
    $("#background_color").on("input", function() {
        let selectedColor = $(this).val();
        $("#colorPreview").css("background-color", selectedColor);
    });
    
    // Enviar el formulario mediante AJAX
    $("#colorForm").on("submit", function(e) {
        e.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "fondo.php",
            data: $(this).serialize(),
            success: function(response) {
                $("#responseMessage").html(response);
            }
        });
    });
});
</script> 
