<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Manejar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inactivity_time'])) {
    // Sanitizar y validar el tiempo de inactividad
    $inactivityTime = (int)$_POST['inactivity_time'];
    
    // Asegurarse de que el valor esté dentro de los límites aceptables
    if ($inactivityTime >= 0 && $inactivityTime <= 60) {
        // Guardar en cookie (duración de 30 días)
        setcookie('inactivity_time', $inactivityTime, time() + (86400 * 30), "/");
        
        // Mensaje de éxito
        echo '<div class="alert alert-success">Tiempo de inactividad actualizado correctamente</div>';
    } else {
        echo '<div class="alert alert-danger">El tiempo debe estar entre 0 y 60 minutos</div>';
    }
    exit();
}

// Obtener el tiempo de inactividad actual de la cookie
$current_inactivity = isset($_COOKIE['inactivity_time']) ? (int)$_COOKIE['inactivity_time'] : 15; // Valor predeterminado: 15 minutos
?>

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="card-title mb-0">Configuración de Tiempo de Inactividad</h5>
        </div>
        <div class="card-body">
            <div id="responseMessage"></div>
            
            <p class="text-muted mb-4">
                Esta configuración determina cuánto tiempo debe pasar sin actividad antes de que se cierre automáticamente tu sesión por motivos de seguridad.
            </p>
            
            <form id="inactivityForm" method="post">
                <div class="mb-4">
                    <label for="inactivity_time" class="form-label">Tiempo de inactividad (en minutos):</label>
                    <div class="input-group">
                        <input 
                            type="range" 
                            class="form-range" 
                            id="inactivity_time" 
                            name="inactivity_time" 
                            min="1" 
                            max="60" 
                            step="1" 
                            value="<?php echo $current_inactivity; ?>"
                            oninput="updateInactivityValue(this.value)"
                        >
                        <span id="inactivityValue" class="ms-3 fw-bold"><?php echo $current_inactivity; ?> minutos</span>
                    </div>
                    <div class="form-text">
                        Mueve el control deslizante para ajustar el tiempo de inactividad:
                        <ul class="mt-2">
                            <li>Valores más bajos (1-5 minutos): Mayor seguridad, pero tendrás que iniciar sesión con más frecuencia.</li>
                            <li>Valores intermedios (10-20 minutos): Buen equilibrio entre seguridad y conveniencia.</li>
                            <li>Valores altos (30-60 minutos): Mayor conveniencia, pero menor seguridad si dejas el dispositivo desatendido.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disableInactivity" <?php echo $current_inactivity == 0 ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="disableInactivity">
                            Desactivar cierre automático de sesión
                        </label>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle"></i> No recomendado.
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Guardar configuración</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Control para desactivar completamente
    $("#disableInactivity").change(function() {
        if($(this).is(":checked")) {
            $("#inactivity_time").val(0).prop('disabled', true);
            $("#inactivityValue").text("Desactivado");
        } else {
            $("#inactivity_time").val(15).prop('disabled', false);
            $("#inactivityValue").text("15 minutos");
        }
    });
    
    // Inicialización para el estado desactivado
    if ($("#inactivity_time").val() == 0) {
        $("#disableInactivity").prop('checked', true);
        $("#inactivity_time").prop('disabled', true);
        $("#inactivityValue").text("Desactivado");
    }
    
    // Enviar el formulario mediante AJAX
    $("#inactivityForm").on("submit", function(e) {
        e.preventDefault();
        
        $.ajax({
            type: "POST",
            url: "inactividad.php",
            data: $(this).serialize(),
            success: function(response) {
                $("#responseMessage").html(response);
            }
        });
    });
});

// Función para actualizar el valor mostrado
function updateInactivityValue(val) {
    if (val == 0) {
        document.getElementById("inactivityValue").textContent = "Desactivado";
    } else {
        document.getElementById("inactivityValue").textContent = val + " minutos";
    }
}
</script>
