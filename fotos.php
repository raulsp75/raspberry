<?php
// Incluir el encabezado del sitio
include 'header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Fotos - Antena WiFi Pringles</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #9b59b6;
        }
        
        h2 {
            color: #9b59b6;
            padding-left: 10px;
            border-left: 5px solid #9b59b6;
        }
        
        .gallery-section {
            margin-bottom: 40px;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 20px;
        }
        
        .gallery-item {
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .image-placeholder {
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #666;
            font-size: 14px;
        }
        
        .gallery-caption {
            padding: 15px;
            background-color: #fff;
            border-top: 1px solid #eee;
        }
        
        .gallery-caption h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
        }
        
        .gallery-caption p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .gallery-nav {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        
        .gallery-nav a {
            display: inline-block;
            margin: 0 10px;
            padding: 8px 15px;
            background-color: #9b59b6;
            color: #fff;
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        
        .gallery-nav a:hover {
            background-color: #8e44ad;
        }
        
        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-camera"></i> Galería de Fotos: Antena WiFi Pringles</h1>
        
        <div class="gallery-nav">
            <a href="#construccion"><i class="fas fa-hammer"></i> Construcción</a>
            <a href="#hardware"><i class="fas fa-microchip"></i> Hardware</a>
            <a href="#pruebas"><i class="fas fa-vial"></i> Pruebas</a>
            <a href="#interfaz"><i class="fas fa-desktop"></i> Interfaz</a>
        </div>
        
        <div class="gallery-section" id="construccion">
            <h2><i class="fas fa-hammer"></i> Proceso de Construcción</h2>
            <div class="gallery-grid">
                <!-- Imagen 1 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Materiales Iniciales]
                    </div>
                    <div class="gallery-caption">
                        <h3>Materiales Iniciales</h3>
                        <p>Conjunto completo de materiales necesarios para la construcción de la antena.</p>
                    </div>
                </div>
                
                <!-- Imagen 2 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Preparación del Contenedor]
                    </div>
                    <div class="gallery-caption">
                        <h3>Preparación del Contenedor</h3>
                        <p>Bote de Pringles preparado con las marcas para realizar el orificio del conector.</p>
                    </div>
                </div>
                
                <!-- Imagen 3 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Instalación del Conector]
                    </div>
                    <div class="gallery-caption">
                        <h3>Instalación del Conector</h3>
                        <p>Proceso de instalación y fijación del conector N en el lateral del bote.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="gallery-section" id="hardware">
            <h2><i class="fas fa-microchip"></i> Configuración del Hardware</h2>
            <div class="gallery-grid">
                <!-- Imagen 4 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Conexión al Adaptador]
                    </div>
                    <div class="gallery-caption">
                        <h3>Conexión al Adaptador</h3>
                        <p>Antena conectada al adaptador WiFi mediante cable coaxial.</p>
                    </div>
                </div>
                
                <!-- Imagen 5 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Montaje en Raspberry Pi]
                    </div>
                    <div class="gallery-caption">
                        <h3>Montaje en Raspberry Pi</h3>
                        <p>Sistema completo con Raspberry Pi, adaptador WiFi y antena montada en soporte.</p>
                    </div>
                </div>
                
                <!-- Imagen 6 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Estación de Trabajo]
                    </div>
                    <div class="gallery-caption">
                        <h3>Estación de Trabajo</h3>
                        <p>Estación de trabajo completa durante el desarrollo del software.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="gallery-section" id="pruebas">
            <h2><i class="fas fa-vial"></i> Pruebas y Rendimiento</h2>
            <div class="gallery-grid">
                <!-- Imagen 7 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Prueba de Alcance]
                    </div>
                    <div class="gallery-caption">
                        <h3>Prueba de Alcance</h3>
                        <p>Prueba de alcance realizada en espacio abierto.</p>
                    </div>
                </div>
                
                <!-- Imagen 8 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Análisis de Señal]
                    </div>
                    <div class="gallery-caption">
                        <h3>Análisis de Señal</h3>
                        <p>Captura de pantalla del software inSSIDer mostrando la potencia de la señal.</p>
                    </div>
                </div>
                
                <!-- Imagen 9 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Prueba de Conectividad]
                    </div>
                    <div class="gallery-caption">
                        <h3>Prueba de Conectividad</h3>
                        <p>Diversos dispositivos conectados simultáneamente a la red de la antena Pringles.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="gallery-section" id="interfaz">
            <h2><i class="fas fa-desktop"></i> Interfaz de Usuario</h2>
            <div class="gallery-grid">
                <!-- Imagen 10 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Página de Inicio]
                    </div>
                    <div class="gallery-caption">
                        <h3>Página de Inicio</h3>
                        <p>Página de inicio de la aplicación web mostrando opciones de registro e inicio de sesión.</p>
                    </div>
                </div>
                
                <!-- Imagen 11 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Panel de Usuario]
                    </div>
                    <div class="gallery-caption">
                        <h3>Panel de Usuario</h3>
                        <p>Panel principal del usuario después de iniciar sesión.</p>
                    </div>
                </div>
                
                <!-- Imagen 12 -->
                <div class="gallery-item">
                    <div class="image-placeholder">
                        [Sección de Juegos]
                    </div>
                    <div class="gallery-caption">
                        <h3>Sección de Juegos</h3>
                        <p>Interfaz de selección de mini juegos disponibles para los usuarios.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Incluye FontAwesome para los iconos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>

<?php
// Incluir el pie de página del sitio
include 'footer.php';
?>
