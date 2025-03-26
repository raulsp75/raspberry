<?php
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
    <title>Estado de la Red - WAN</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
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
        
        .connection-status {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
        }
        
        .status-text {
            font-size: 14px;
            font-weight: 500;
        }
        
        .connected {
            background-color: #2ecc71;
        }
        
        .disconnected {
            background-color: #e74c3c;
        }
        
        .info-panel {
            padding: 20px;
        }
        
        .info-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .info-header {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            font-weight: 500;
        }
        
        .info-content {
            padding: 15px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            flex: 0 0 170px;
            font-weight: 500;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            font-family: 'Consolas', monospace;
            background-color: #f0f0f0;
            padding: 5px 10px;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }
        
        .dns-section {
            margin-top: 20px;
        }
        
        .dns-title {
            font-weight: 500;
            color: #555;
            margin-bottom: 10px;
        }
        
        .dns-servers {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }
        
        .dns-server {
            font-family: 'Consolas', monospace;
            margin-bottom: 5px;
        }
        
        .dns-server:last-child {
            margin-bottom: 0;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
        }
        
        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-label {
                margin-bottom: 5px;
            }
            
            .connection-status {
                position: static;
                justify-content: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php
    // Ejecutar el comando para obtener información de la interfaz WAN en Linux (Raspberry Pi)
    $wan_info = shell_exec("ifconfig eth0");
    $gateway_info = shell_exec("ip route");
    
    // Parsear la información para obtener la IP y el estado de la conexión
    preg_match('/inet ([0-9.]+)/', $wan_info, $matches);
    $ip = isset($matches[1]) ? $matches[1] : "No disponible";
    
    preg_match('/netmask ([0-9.]+)/', $wan_info, $matches);
    $netmask = isset($matches[1]) ? $matches[1] : "No disponible";
    
    preg_match('/default via ([0-9.]+)/', $gateway_info, $matches);
    $gateway = isset($matches[1]) ? $matches[1] : "No disponible";
    
    // Verificar conexión a Internet
    $connected = strpos(shell_exec("ping -c 1 google.com"), "1 received") !== false;
    
    // Obtener información de los servidores DNS
    $dns_servers = [];
    if (file_exists("/etc/resolv.conf")) {
        $dns_info = file("/etc/resolv.conf", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($dns_info as $line) {
            if (strpos($line, "nameserver") === 0) {
                $dns_servers[] = trim(substr($line, strlen("nameserver")));
            }
        }
    }
    
    // Obtener información adicional de la interfaz
    preg_match('/ether ([0-9a-f:]+)/i', $wan_info, $matches);
    $mac = isset($matches[1]) ? $matches[1] : "No disponible";
    
    preg_match('/RX packets:?(\d+)/', $wan_info, $matches);
    $rx_packets = isset($matches[1]) ? $matches[1] : "No disponible";
    
    preg_match('/TX packets:?(\d+)/', $wan_info, $matches);
    $tx_packets = isset($matches[1]) ? $matches[1] : "No disponible";
    ?>
    
    <div class="container">
        <div class="header">
            <h2>Estado de la Red - WAN</h2>
            <div class="connection-status">
                <div class="status-indicator <?php echo $connected ? 'connected' : 'disconnected'; ?>"></div>
                <div class="status-text"><?php echo $connected ? 'Conectado' : 'Desconectado'; ?></div>
            </div>
        </div>
        
        <div class="info-panel">
            <div class="info-card">
                <div class="info-header">Información de Red</div>
                <div class="info-content">
                    <div class="info-row">
                        <div class="info-label">Dirección IP:</div>
                        <div class="info-value"><?php echo $ip; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Máscara de subred:</div>
                        <div class="info-value"><?php echo $netmask; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Puerta de enlace:</div>
                        <div class="info-value"><?php echo $gateway; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dirección MAC:</div>
                        <div class="info-value"><?php echo $mac; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-header">Estadísticas de Tráfico</div>
                <div class="info-content">
                    <div class="info-row">
                        <div class="info-label">Paquetes Recibidos:</div>
                        <div class="info-value"><?php echo $rx_packets; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Paquetes Enviados:</div>
                        <div class="info-value"><?php echo $tx_packets; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado de Internet:</div>
                        <div class="info-value" style="color: <?php echo $connected ? '#2ecc71' : '#e74c3c'; ?>">
                            <?php echo $connected ? 'Conectado a Internet' : 'Sin conexión a Internet'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($dns_servers)): ?>
                <div class="info-card">
                    <div class="info-header">Servidores DNS</div>
                    <div class="info-content">
                        <?php foreach ($dns_servers as $index => $server): ?>
                            <div class="info-row">
                                <div class="info-label">Servidor DNS <?php echo $index + 1; ?>:</div>
                                <div class="info-value"><?php echo $server; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            Última actualización: <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </div>
</body>
</html>

<?php
// Incluir el pie de página del sitio si existe
if (file_exists('footer.php')) {
    include 'footer.php';
}
?>
