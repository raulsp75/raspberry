<?php
// Incluir el encabezado del sitio
include 'header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación - Antena WiFi Pringles</title>
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
            border-bottom: 2px solid #3498db;
        }
        
        h2 {
            color: #3498db;
            margin-top: 30px;
            padding-left: 10px;
            border-left: 5px solid #3498db;
            font-size: 24px;
        }
        
        h3 {
            color: #2980b9;
            margin-top: 20px;
            font-size: 20px;
        }
        
        p, li {
            margin-bottom: 10px;
        }
        
        ul {
            padding-left: 20px;
        }
        
        li {
            position: relative;
            padding-left: 15px;
            list-style-type: none;
        }
        
        li:before {
            content: "•";
            color: #3498db;
            position: absolute;
            left: 0;
        }
        
        .section {
            margin-bottom: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .toc {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .image-placeholder {
            background-color: #f0f0f0;
            text-align: center;
            padding: 40px;
            border-radius: 5px;
            margin: 20px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-book"></i> Documentación Técnica: Antena WiFi Pringles</h1>
        
        <!-- Tabla de Contenidos -->
        <div class="toc" id="tabla-contenidos">
            <h3><i class="fas fa-list"></i> Tabla de Contenidos</h3>
            <ul>
                <li><a href="#fundamentos">Fundamentos Teóricos</a></li>
                <li><a href="#construccion">Proceso de Construcción</a></li>
                <li><a href="#software">Configuración del Software</a></li>
                <li><a href="#funcionalidades">Funcionalidades</a></li>
                <li><a href="#rendimiento">Rendimiento y Pruebas</a></li>
                <li><a href="#limitaciones">Limitaciones y Mejoras</a></li>
                <li><a href="#referencias">Referencias</a></li>
            </ul>
        </div>
        
        <!-- Sección 1: Fundamentos -->
        <div class="section" id="fundamentos">
            <h2><i class="fas fa-atom"></i> Fundamentos Teóricos</h2>
            
            <h3>Principio de Funcionamiento</h3>
            <p>La antena WiFi Pringles funciona como guía de ondas cilíndrica.</p>
            
            <div class="image-placeholder">
                [Imagen: Diagrama de funcionamiento]
            </div>
            
            <h3>Bases Físicas</h3>
            <ul>
                <li><strong>Frecuencia:</strong> 2.4 GHz</li>
                <li><strong>Longitud de onda:</strong> 12.5 cm</li>
                <li><strong>Ganancia:</strong> 8-10 dBi</li>
            </ul>
        </div>
        
        <!-- Sección 2: Construcción -->
        <div class="section" id="construccion">
            <h2><i class="fas fa-hammer"></i> Proceso de Construcción</h2>
            
            <h3>1. Preparación del Cilindro</h3>
            <p>Limpieza y marcado de posiciones.</p>
            
            <h3>2. Instalación del Conector</h3>
            <p>Fijación del conector N.</p>
            
            <h3>3. Elemento Activo</h3>
            <p>Instalación de la varilla de cobre.</p>
            
            <h3>4. Conexiones</h3>
            <p>Conexión al adaptador WiFi.</p>
        </div>
        
        <!-- Sección 3: Software -->
        <div class="section" id="software">
            <h2><i class="fas fa-code"></i> Configuración del Software</h2>
            
            <h3>Sistema Operativo</h3>
            <p>Configuración de Raspberry Pi OS.</p>
            
            <h3>Configuración de Red</h3>
            <p>Configuración de punto de acceso WiFi.</p>
            
            <h3>Servidor Web</h3>
            <p>Implementación con Apache y PHP.</p>
        </div>
        
        <!-- Sección 4: Funcionalidades -->
        <div class="section" id="funcionalidades">
            <h2><i class="fas fa-cogs"></i> Funcionalidades</h2>
            
            <h3>Sistema de Usuarios</h3>
            <p>Registro, login y gestión de perfiles.</p>
            
            <h3>Panel de Red</h3>
            <p>Información de conexión y rendimiento.</p>
            
            <h3>Mini Juegos</h3>
            <p>Tres juegos implementados para los usuarios.</p>
            
            <h3>Personalización</h3>
            <p>Opciones de configuración mediante cookies.</p>
        </div>
        
        <!-- Sección 5: Rendimiento -->
        <div class="section" id="rendimiento">
            <h2><i class="fas fa-chart-line"></i> Rendimiento y Pruebas</h2>
            
            <h3>Pruebas de Alcance</h3>
            <p>Resultados de mediciones de cobertura.</p>
            
            <h3>Pruebas de Carga</h3>
            <p>Evaluación con múltiples usuarios.</p>
        </div>
        
        <!-- Sección 6: Limitaciones -->
        <div class="section" id="limitaciones">
            <h2><i class="fas fa-exclamation-triangle"></i> Limitaciones y Mejoras</h2>
            
            <h3>Limitaciones Actuales</h3>
            <p>Principales restricciones del sistema.</p>
            
            <h3>Mejoras Futuras</h3>
            <p>Propuestas para versiones posteriores.</p>
        </div>
        
        <!-- Sección 7: Referencias -->
        <div class="section" id="referencias">
            <h2><i class="fas fa-book"></i> Referencias</h2>
            <p>Fuentes consultadas y bibliografía.</p>
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
