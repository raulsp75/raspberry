<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Raspberry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="d-flex vh-100">
        <aside class="bg-white shadow p-4 w-25">
            <h2 class="text-primary fw-bold text-center">Menú Principal</h2>
            <nav class="nav flex-column mt-4">
                <a href="#" class="nav-link text-dark menu-item" data-section="inicio">Inicio</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="red">Red</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="minijuegos">Minijuegos</a>
                <a href="#" class="nav-link text-dark menu-item" data-section="configuracion">Configuración</a>
                <a href="login.php" class="nav-link text-danger">Cerrar sesión</a>
            </nav>
        </aside>

        <!-- Contenido principal -->
        <main class="flex-grow-1 p-4">
            <!-- Barra superior con opciones -->
            <div id="barraSuperior" class="bg-white p-3 rounded shadow d-flex gap-3 mb-4 overflow-auto" style="display: none;">
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="hardware.php">Hardware</button>
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="wan.php">WAN</button>
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="lan.php">LAN</button>
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="estado.php">Estado</button>
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="adivina.php">Adivíname</button>
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="romano.php">Romano</button>
                <button class="btn btn-outline-secondary btn-categoria" data-pagina="circulo.php" style="display: none;">Atrapa el Círculo</button>
            </div>

            <!-- Contenido dinámico -->
            <div id="contenido" class="row g-4">
                <p>Selecciona una opción del menú</p>
            </div>
        </main>
    </div>

    <script>
        $(document).ready(function(){
            $(".menu-item").click(function(){
                let section = $(this).data("section");
                sessionStorage.setItem("currentSection", section);

                if (section === "red") {
                    $("#barraSuperior").fadeIn();
                    $("#contenido").html("<p>Selecciona una opción de Red</p>");
                    $(".btn-categoria").show();
                    $(".btn-categoria[data-pagina='adivina.php']").hide();
                    $(".btn-categoria[data-pagina='romano.php']").hide();
                    $(".btn-categoria[data-pagina='circulo.php']").hide();
                } else if (section === "minijuegos") {
                    $("#barraSuperior").fadeIn();
                    $(".btn-categoria").hide();
                    $(".btn-categoria[data-pagina='adivina.php']").show();
                    $(".btn-categoria[data-pagina='romano.php']").show();
                    $(".btn-categoria[data-pagina='circulo.php']").show();
                    $("#contenido").html("<p>Selecciona un minijuego</p>");
                } else {
                    $("#barraSuperior").fadeOut();
                    $("#contenido").html("<p>Selecciona una opción del menú</p>");
                }

                $(".menu-item").removeClass("text-danger fw-bold");
                $(this).addClass("text-danger fw-bold");
            });

            $(".btn-categoria").click(function(){
                $(".btn-categoria").removeClass("btn-primary").addClass("btn-outline-secondary");
                $(this).removeClass("btn-outline-secondary").addClass("btn-primary");
                let pagina = $(this).data("pagina");
                cargarContenido(pagina);
            });

            function cargarContenido(pagina) {
                $.ajax({
                    url: pagina,
                    success: function(data) {
                        $("#contenido").html(data);
                    }
                });
            }
        });
    </script>
</body>
</html>

