<?php
session_start();

// Manejar el cambio de preferencias de fuente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar tamaño de fuente
    if (isset($_POST['font_size'])) {
        $fontSize = htmlspecialchars($_POST['font_size']);
        setcookie('font_size', $fontSize, time() + (86400 * 30), "/");
    }
    
    // Actualizar tipo de fuente
    if (isset($_POST['font_family'])) {
        $fontFamily = htmlspecialchars($_POST['font_family']);
        setcookie('font_family', $fontFamily, time() + (86400 * 30), "/");
    }
    
    // Mensaje de éxito
    echo '<div class="alert alert-success">Configuración de fuente actualizada correctamente</div>';
    echo '<script>
            setTimeout(function() {
                $("body").css({
                    "font-family": "' . ($fontFamily ?? $_COOKIE['font_family'] ?? 'inherit') . '",
                    "font-size": "' . ($fontSize ?? $_COOKIE['font_size'] ?? '1rem') . '"
                });
            }, 300);
          </script>';
    exit();
}

// Obtener valores actuales
$current_size = isset($_COOKIE['font_size']) ? $_COOKIE['font_size'] : '1rem';
$current_font = isset($_COOKIE['font_family']) ? $_COOKIE['font_family'] : 'Arial, sans-serif';
?>

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuración de Letra</h5>
        </div>
        <div class="card-body">
            <div id="responseMessage"></div>
            
            <form id="fontForm" method="post">
                <div class="row">
                    <!-- Configuración de tamaño de letra -->
                    <div class="col-md-6 mb-3">
                        <label for="font_size" class="form-label">Tamaño de letra:</label>
                        <select class="form-select" id="font_size" name="font_size">
                            <option value="0.875rem" <?php echo ($current_size == '0.875rem') ? 'selected' : ''; ?>>Pequeño</option>
                            <option value="1rem" <?php echo ($current_size == '1rem') ? 'selected' : ''; ?>>Normal</option>
                            <option value="1.125rem" <?php echo ($current_size == '1.125rem') ? 'selected' : ''; ?>>Grande</option>
                            <option value="1.25rem" <?php echo ($current_size == '1.25rem') ? 'selected' : ''; ?>>Muy grande</option>
                        </select>
                        <div class="form-text">Selecciona el tamaño de texto que prefieras.</div>
                    </div>
                    
                    <!-- Configuración de tipo de letra -->
                    <div class="col-md-6 mb-3">
                        <label for="font_family" class="form-label">Tipo de letra:</label>
                        <select class="form-select" id="font_family" name="font_family">
                            <option value="Arial, sans-serif" <?php echo ($current_font == 'Arial, sans-serif') ? 'selected' : ''; ?>>Arial</option>
                            <option value="'Times New Roman', serif" <?php echo ($current_font == "'Times New Roman', serif") ? 'selected' : ''; ?>>Times New Roman</option>
                            <option value="'Courier New', monospace" <?php echo ($current_font == "'Courier New', monospace") ? 'selected' : ''; ?>>Courier New</option>
                            <option value="Georgia, serif" <?php echo ($current_font == "Georgia, serif") ? 'selected' : ''; ?>>Georgia</option>
                            <option value="'Segoe UI', sans-serif" <?php echo ($current_font == "'Segoe UI', sans-serif") ? 'selected' : ''; ?>>Segoe UI</option>
                        </select>
                        <div class="form-text">Selecciona el tipo de letra que prefieras.</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Vista previa:</label>
                    <div id="fontPreview" class="p-4 rounded border" style="font-family: <?php echo $current_font; ?>; font-size: <?php echo $current_size; ?>;">
                        <p class="mb-0">Este texto muestra cómo se verá tu panel con el tipo y tamaño de letra seleccionados. Puedes modificar estos valores según tus preferencias para personalizar la apariencia del texto en toda la aplicación.</p>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Actualizar vista previa cuando se cambian las opciones
    $("#font_size, #font_family").on("change", function() {
        let selectedSize = $("#font_size").val();
        let selectedFont = $("#font_family").val();
        
        $("#fontPreview").css({
            "font-size": selectedSize,
            "font-family": selectedFont
        });
    });
    
    // Enviar el formulario mediante AJAX
    $("#fontForm").on("submit", function(e) {
        e.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "letra.php",
            data: $(this).serialize(),
            success: function(response) {
                $("#responseMessage").html(response);
            }
        });
    });
});
</script>
