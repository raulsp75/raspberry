<?php
// Inicialización de variables
$salida = $titulo = "";

// Información predeterminada
$titulo = "Panel de Control del Sistema";

// Obtener información del sistema
$hostname = shell_exec("hostname");
$uptime = shell_exec("uptime");
$date = shell_exec("date");
$temp = shell_exec("vcgencmd measure_temp");
$memory = shell_exec("free -h");
$cpu_usage = shell_exec("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\\([0-9.]*\\)%* id.*/\\1/' | awk '{print 100 - $1\"%\"}'");

// Variable para guardar el estado
$system_status = "normal";

// Determinar el estado del sistema basado en la temperatura
if (preg_match('/temp=([0-9.]+)/', $temp, $matches)) {
    $temperature = floatval($matches[1]);
    if ($temperature > 70) {
        $system_status = "critical";
    } else if ($temperature > 60) {
        $system_status = "warning";
    }
}

// Incluir el encabezado del sitio si existe
if (file_exists('header.php')) {
    include 'header.php';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Equipo - Antena WiFi Pringles</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #34495e, #2980b9);
            color: #fff;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 500;
        }
        
        .content-area {
            padding: 20px;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .metric-icon {
            font-size: 32px;
            margin-bottom: 10px;
            color: #3498db;
        }
        
        .temperature-status {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            background-color: #2ecc71;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        
        .metric-value {
            font-size: 26px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .system-status {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        
        .status-normal {
            background-color: #e8f7f0;
            border-left: 4px solid #2ecc71;
        }
        
        .status-warning {
            background-color: #fef9e7;
            border-left: 4px solid #f39c12;
        }
        
        .status-critical {
            background-color: #fdedec;
            border-left: 4px solid #e74c3c;
        }
        
        .status-icon-large {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .icon-normal {
            color: #2ecc71;
        }
        
        .icon-warning {
            color: #f39c12;
        }
        
        .icon-critical {
            color: #e74c3c;
        }
        
        .status-message {
            flex: 1;
        }
        
        .status-title {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: 500;
        }
        
        .status-description {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        
        .output-panel {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .output-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .output-title {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .output-content {
            font-family: 'Consolas', monospace;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            line-height: 1.5;
        }
        
        pre {
            margin: 0;
            white-space: pre-wrap;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><?php echo $titulo; ?></h2>
        </div>
        
        <div class="content-area">
            <div class="system-status status-<?php echo $system_status; ?>">
                <div class="status-icon-large icon-<?php echo $system_status; ?>">
                    <?php if ($system_status == 'normal'): ?>
                        <i class="fas fa-check-circle"></i>
                    <?php elseif ($system_status == 'warning'): ?>
                        <i class="fas fa-exclamation-triangle"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php endif; ?>
                </div>
                <div class="status-message">
                    <h3 class="status-title">
                        <?php 
                            if ($system_status == 'normal') echo "Sistema funcionando correctamente";
                            elseif ($system_status == 'warning') echo "Advertencia: Temperatura elevada";
                            else echo "¡Alerta! Temperatura crítica";
                        ?>
                    </h3>
                    <p class="status-description">
                        <?php 
                            if ($system_status == 'normal') echo "Todos los parámetros están dentro de los valores normales.";
                            elseif ($system_status == 'warning') echo "La temperatura del sistema está por encima de lo recomendado. Considere mejorar la ventilación.";
                            else echo "¡La temperatura es peligrosamente alta! Apague el sistema para evitar daños.";
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="dashboard">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div class="metric-value"><?php echo trim($cpu_usage); ?></div>
                    <div class="metric-label">USO DE CPU</div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-thermometer-half"></i>
                    </div>
                    <div class="metric-value"><?php echo trim(str_replace("temp=", "", $temp)); ?></div>
                    <div class="metric-label">TEMPERATURA</div>
                    <div class="temperature-status">
                        <div class="status-icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-memory"></i>
                    </div>
                    <div class="metric-value">
                        <?php 
                            preg_match('/Mem:.+?(\d+[GMK])/', $memory, $matches);
                            echo isset($matches[1]) ? $matches[1] : "N/A"; 
                        ?>
                    </div>
                    <div class="metric-label">MEMORIA USADA</div>
                </div>
            </div>
            
            <?php if (!empty($salida)): ?>
                <div class="output-panel">
                    <div class="output-header">
                        <h3 class="output-title">Resultado:</h3>
                    </div>
                    <div class="output-content">
                        <?php echo $salida; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            Última actualización: <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </div>
    
    <!-- Incluye FontAwesome para los iconos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>

<?php
// Incluir el pie de página del sitio si existe
if (file_exists('footer.php')) {
    include 'footer.php';
}
?>
