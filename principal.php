<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

// Comprobar si el usuario es administrador
$es_admin = false;
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    $es_admin = true;
}

// Get cookie preferences
$background_color = $_COOKIE['background_color'] ?? '#f8f9fa';
$font_size = $_COOKIE['font_size'] ?? '1rem';
$font_family = $_COOKIE['font_family'] ?? 'Arial, sans-serif';
$inactivity_time = (int)($_COOKIE['inactivity_time'] ?? 15);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Raspberry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        body {
            background-color: <?php echo $background_color; ?>;
            background-image: url('fondo2.png');
            background-size: 50%;
            background-position: 100% 100%;
            background-repeat: no-repeat;
            font-family: <?php echo $font_family; ?>;
            font-size: <?php echo $font_size; ?>;
        }
        main { display: flex; flex-direction: column; }
        #contenido { margin-top: 1rem; }
        #barraSuperior { justify-content: flex-start; align-items: center; flex-wrap: wrap; }
        .btn-categoria, .btn-asignatura {
            min-width: 120px;
            text-align: center;
            margin: 0.25rem;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            height: 38px;
        }
        .btn-primary { box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); }
    </style>
<head/>
<body>
    <div class="d-flex vh-100">
        <aside class="bg-white shadow p-4 w-25">
            <h2 class="text-primary fw-bold text-center">Menú Principal</h2>
            <nav class="nav flex-column mt-4">
                <a href="#" class="nav-link text-dark menu-item" data-section="inicio">Inicio</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="red">Red</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="minijuegos">Minijuegos</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="foro">Foro</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="guia">Guia Pringles</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="configuracion">Configuración</a>
                <?php if ($es_admin): ?>
                <a href="#" class="nav-link text-dark menu-item" data-section="admin">Administrador</a>
                <?php endif; ?>
                <a href="login.php" class="nav-link text-danger">Cerrar sesión</a>
            </nav>
        </aside>

        <main class="flex-grow-1 p-4">
            <div id="barraSuperior" class="bg-white p-3 rounded shadow" style="display: none;"></div>
            <div id="contenido">
                <div class="alert alert-info">
                    <h4 class="alert-heading">¡Bienvenido a tu Centro de Control!</h4>
                    <p>Explora las opciones del menú y descubre todo lo que puedes hacer.</p>
                    <hr>
                    <p class="mb-0">Selecciona una categoría para comenzar tu aventura digital.</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de advertencia de inactividad -->
    <div class="modal fade" id="inactivityModal" tabindex="-1" aria-labelledby="inactivityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="inactivityModalLabel">Advertencia de inactividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tu sesión está a punto de expirar por inactividad.</p>
                    <p>Se cerrará la sesión en <span id="countdownTimer" class="fw-bold">30</span> segundos.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continuar sesión</button>
                    <a href="login.php" class="btn btn-danger">Cerrar sesión ahora</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            // Added this to prevent undefined error
            const asignaturas = {
                'primero': [],
                'segundo': []
            };
            
            // Menú principal - click handler
            $(".menu-item").click(function(){
                let section = $(this).data("section");
                sessionStorage.setItem("currentSection", section);
                limpiarBarraSuperior();

                switch(section) {
                    case "red":
                        $("#barraSuperior").append(`
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="wan.php">WAN</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="lan.php">LAN</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="estado.php">Estado</button>
                        `).show();
                        $("#contenido").html(`
                            <div class="alert alert-primary">
                                <h4 class="alert-heading">🌐 Panel de Conectividad de Red</h4>
                                <p>Explora y gestiona tus configuraciones de red. Elige entre WAN, LAN o revisa el estado actual de tu conexión.</p>
                            </div>
                        `);
                        break;
                    case "minijuegos":
                        $("#barraSuperior").append(`
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="adivina.php">Adivíname</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="romano.php">Romano</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="circulo.php">Atrapa el Círculo</button>
                        `).show();
                        $("#contenido").html(`
                            <div class="alert alert-success">
                                <h4 class="alert-heading">🎮 Zona de Entretenimiento</h4>
                                <p>Elige tu desafío: Adivina el juego, Números Romanos o Atrapa el Círculo. ¡Diversión garantizada!</p>
                            </div>
                        `);
                        break;
                    case "guia":
                        $("#barraSuperior").append(`
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="materiales.php">Materiales</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="documentacion.php">Documentación</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="fotos.php">Fotos</button>
                        `).show();
                        $("#contenido").html(`
                            <div class="alert alert-warning">
                                <h4 class="alert-heading">📚 Guía de Recursos Pringles</h4>
                                <p>Explora materiales, documentación y galería de fotos. Tu recurso definitivo de información.</p>
                            </div>
                        `);
                        break;
                    case "configuracion":
                        $("#barraSuperior").append(`
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="fondo.php">Fondo</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="letra.php">Letra</button>
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="inactividad.php">Inactividad</button>
                        `).show();
                        $("#contenido").html(`
                            <div class="alert alert-secondary">
                                <h4 class="alert-heading">⚙️ Centro de Personalización</h4>
                                <p>Ajusta tu experiencia: cambia fondos, tamaño de letra y configura tus preferencias de inactividad.</p>
                            </div>
                        `);
                        break;
                    case "foro":
                        $("#barraSuperior").append(`
                            <button class="btn btn-outline-secondary btn-categoria" data-pagina="foro.php">Foro</button>
                        `).show();
                        $("#contenido").html(`
                            <div class="alert alert-info">
                                <h4 class="alert-heading">💬 Espacio de Comunidad</h4>
                                <p>Conéctate, comparte y participa en nuestro foro. Tu voz es importante.</p>
                            </div>
                        `);
                        break;
		            case "admin":
                        window.location.href = 'admin.php';
    		            return;
                    default:
                        $("#barraSuperior").hide();
                        $("#contenido").html(`
                            <div class="alert alert-info">
                                <h4 class="alert-heading">🚀 Centro de Control</h4>
                                <p>Tu panel de control personalizado. Elige una categoría para comenzar.</p>
                            </div>
                        `);
                }

                // Resaltar opción seleccionada
                $(".menu-item").removeClass("text-danger fw-bold");
                $(this).addClass("text-danger fw-bold");

                removeBlanks();
                asignarEventos();
            });
            
            // Función para eliminar espacios en blanco
            function removeBlanks() {
                $("#barraSuperior").nextUntil("#contenido").filter(function() {
                    return !$.trim($(this).html()) || $(this).hasClass("bg-white");
                }).remove();
            }
            
            // Limpiar barra superior
            function limpiarBarraSuperior() {
                $("#barraSuperior").empty().hide();
                removeBlanks();
            }

            // Asignar eventos a botones
            function asignarEventos() {
                $(".btn-categoria").off("click").on("click", function() {
                    if ($(this).hasClass("examen-curso")) {
                        // Commented out function due to lack of course data
                        // mostrarAsignaturas($(this).data("curso"));
                        return;
                    } else {
                        $(".btn-categoria").removeClass("btn-primary").addClass("btn-outline-secondary");
                        $(this).removeClass("btn-outline-secondary").addClass("btn-primary");
                        cargarContenido($(this).data("pagina"));
                    }
                });
            }

            // Cargar contenido genérico
            function cargarContenido(pagina) {
                $.ajax({
                    url: pagina,
                    success: function(data) {
                        $("#contenido").html(data);
                    },
                    error: function() {
                        $("#contenido").html(`
                            <div class="alert alert-danger">
                                <h4 class="alert-heading">Error de carga</h4>
                                <p>No se pudo cargar el contenido solicitado. Por favor, inténtalo de nuevo más tarde.</p>
                            </div>
                        `);
                    },
                    complete: function() {
                        removeBlanks();
                    }
                });
            }
            
            // Restaurar sección guardada
            let savedSection = sessionStorage.getItem("currentSection");
            if (savedSection) {
                // Si la sección guardada es "admin" pero el usuario no es admin, no restaurarla
                if (savedSection === "admin" && !<?php echo $es_admin ? 'true' : 'false'; ?>) {
                    sessionStorage.removeItem("currentSection");
                } else {
                    $(`.menu-item[data-section="${savedSection}"]`).click();
                }
            }
            
            // Control de inactividad
            if (inactivityTime > 0) {
                resetInactivityTimer();
                $(document).on('mousemove keypress click', resetInactivityTimer);
            }
            
            let inactivityTimeout;
            let modalCountdown;
            let countdownValue = 30;
            
            function resetInactivityTimer() {
                clearTimeout(inactivityTimeout);
                let inactivityMs = (<?php echo $inactivity_time; ?> * 60 * 1000) - (countdownValue * 1000);
                inactivityTimeout = setTimeout(showInactivityWarning, inactivityMs);
            }
            
            function showInactivityWarning() {
                let inactivityModal = new bootstrap.Modal(document.getElementById('inactivityModal'));
                inactivityModal.show();
                
                countdownValue = 30;
                $('#countdownTimer').text(countdownValue);
                
                modalCountdown = setInterval(function() {
                    countdownValue--;
                    $('#countdownTimer').text(countdownValue);
                    
                    if (countdownValue <= 0) {
                        clearInterval(modalCountdown);
                        window.location.href = 'login.php';
                    }
                }, 1000);

                $('#inactivityModal').on('hidden.bs.modal', function() {
                    clearInterval(modalCountdown);
                    resetInactivityTimer();
                });
            }
        });
    </script>
</body>
</html>
