<?php
// Incluir el encabezado del sitio
include 'header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materiales - Antena WiFi Pringles</title>
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
            border-bottom: 2px solid #e74c3c;
        }
        
        h2 {
            color: #e74c3c;
            margin-top: 30px;
            padding-left: 10px;
            border-left: 5px solid #e74c3c;
        }
        
        ul {
            padding-left: 20px;
        }
        
        li {
            margin-bottom: 10px;
            list-style-type: none;
            position: relative;
            padding-left: 25px;
        }
        
        li:before {
            content: "▹";
            color: #e74c3c;
            position: absolute;
            left: 0;
            font-size: 18px;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .note {
            background-color: #f0f0f0;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin: 20px 0;
            font-style: italic;
        }
        
        .header-icon {
            margin-right: 10px;
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tools header-icon"></i>Materiales para la Antena WiFi Pringles</h1>
        
        <div class="section">
            <h2><i class="fas fa-microchip header-icon"></i>Componentes Principales</h2>
            <ul>
                <li>1 bote de Pringles vacío (preferiblemente de tamaño estándar)</li>
                <li>1 adaptador WiFi USB con antena externa desmontable</li>
                <li>1 conector N hembra para montaje en panel</li>
                <li>1 cable coaxial RG-58 de 15 cm</li>
                <li>1 varilla de cobre de 3.5 mm de diámetro y 31 mm de longitud (elemento activo)</li>
            </ul>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-wrench header-icon"></i>Herramientas Necesarias</h2>
            <ul>
                <li>Taladro con brocas de diferentes diámetros</li>
                <li>Soldador y estaño</li>
                <li>Alicates</li>
                <li>Cúter o tijeras</li>
                <li>Regla y compás</li>
                <li>Lápiz o rotulador permanente</li>
                <li>Multímetro (opcional, para comprobar conexiones)</li>
            </ul>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-plus-circle header-icon"></i>Materiales Adicionales</h2>
            <ul>
                <li>4 tornillos pequeños con tuercas para fijar el conector N</li>
                <li>Cinta aislante</li>
                <li>Silicona o pegamento resistente al agua (para sellar)</li>
                <li>Placa metálica circular de 86 mm de diámetro (opcional, para mayor eficiencia)</li>
                <li>Pintura en spray (opcional, para personalización)</li>
            </ul>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-laptop header-icon"></i>Equipo para Pruebas</h2>
            <ul>
                <li>Ordenador con sistema operativo compatible</li>
                <li>Software para análisis de redes WiFi (como inSSIDer, Wireshark o NetSpot)</li>
                <li>Dispositivo móvil para pruebas de conexión</li>
            </ul>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-microchip header-icon"></i>Componentes Electrónicos para Funcionalidades Adicionales</h2>
            <ul>
                <li>Raspberry Pi o similar (para crear el punto de acceso)</li>
                <li>Tarjeta microSD (16GB o más)</li>
                <li>Cable de alimentación USB</li>
                <li>Adaptador de corriente</li>
            </ul>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-globe header-icon"></i>Recursos Online</h2>
            <ul>
                <li>Imagen de sistema operativo para Raspberry Pi</li>
                <li>Librerías y código fuente para la página web</li>
                <li>Drivers actualizados para el adaptador WiFi</li>
            </ul>
        </div>
        
        <div class="section">
            <h2><i class="fas fa-shield-alt header-icon"></i>Consideraciones de Seguridad</h2>
            <ul>
                <li>Guantes de protección</li>
                <li>Gafas de seguridad para taladrado</li>
                <li>Espacio de trabajo bien ventilado</li>
            </ul>
        </div>
        
        <div class="note">
            <p>Nota: Las especificaciones exactas pueden variar según la frecuencia de operación deseada y el adaptador WiFi utilizado.</p>
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
