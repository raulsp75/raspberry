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
    <title>Estado de la Red - LAN</title>
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
            background: linear-gradient(135deg, #27ae60, #2ecc71);
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
        
        .network-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
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
            background-color: #2ecc71;
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
            border-left: 3px solid #2ecc71;
        }
        
        .status-chip {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 15px;
            color: white;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #2ecc71;
        }
        
        .status-inactive {
            background-color: #e74c3c;
        }
        
        .wifi-info {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
            border-left: 3px solid #2ecc71;
        }
        
        .wifi-icon {
            flex: 0 0 40px;
            height: 40px;
            background-color: #2ecc71;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .wifi-details {
            flex: 1;
        }
        
        .wifi-ssid {
            font-weight: 500;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .wifi-security {
            font-size: 12px;
            color: #666;
        }
        
        .dhcp-range {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .dhcp-endpoint {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .dhcp-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .dhcp-ip {
            font-family: 'Consolas', monospace;
            font-weight: 500;
        }
        
        .dhcp-arrow {
            display: flex;
            align-items: center;
            color: #aaa;
            padding: 0 15px;
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
            
            .network-badge {
                position: static;
                display: inline-block;
                margin-top: 10px;
            }
            
            .dhcp-range {
                flex-direction: column;
            }
            
            .dhcp-arrow {
                transform: rotate(90deg);
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <?php
    // Obtener información de la interfaz LAN (WLAN1)
    $wan_info = shell_exec("ifconfig wlan1");
    
    // Parsear la información para obtener la IP, la máscara de subred
    preg_match('/inet ([0-9.]+) netmask ([0-9.]+)/', $wan_info, $matches);
    $ip = isset($matches[1]) ? $matches[1] : "No disponible";
    $netmask = isset($matches[2]) ? $matches[2] : "No disponible";
    
    // Obtener información de la puerta de enlace
    $gateway_info = shell_exec("ip route");
    preg_match('/default via ([0-9.]+)/', $gateway_info, $matches);
    $gateway = isset($matches[1]) ? $matches[1] : "No disponible";
    
    // Verificar conexión a Internet
    $connected = strpos(shell_exec("ping -c 1 google.com"), '1 received') !== false;
    
    // Obtener información adicional de la interfaz
    preg_match('/ether ([0-9a-f:]+)/i', $wan_info, $matches);
    $mac = isset($matches[1]) ? $matches[1] : "No disponible";
    
    // Obtener el SSID del archivo hostapd.conf
    $ssid = "No disponible";
    $security_method = "No disponible";
    if (file_exists("/etc/hostapd/hostapd.conf")) {
        $hostapd_config = file_get_contents("/etc/hostapd/hostapd.conf");
        
        if (preg_match('/^ssid=(.*)$/m', $hostapd_config, $matches)) {
            $ssid = htmlspecialchars($matches[1]);
        }
        
        if (preg_match('/wpa_key_mgmt=([^\s]+)/', $hostapd_config, $matches)) {
            $security_method = htmlspecialchars($matches[1]);
        }
    }
    
    // Obtener el rango de direcciones DHCP
    $dhcp_start = "No disponible";
    $dhcp_end = "No disponible";
    if (file_exists("/etc/dnsmasq.conf")) {
        $dhcp_config = file_get_contents("/etc/dnsmasq.conf");
        
        if (preg_match('/dhcp-range=([0-9.]+),([0-9.]+)/', $dhcp_config, $matches)) {
            $dhcp_start = $matches[1];
            $dhcp_end = $matches[2];
        }
    }
    
    // Obtener información sobre clientes conectados (ejemplo simplificado)
    $clients_info = shell_exec("arp -n");
    preg_match_all('/([0-9.]+).+([0-9a-f:]{17})/i', $clients_info, $matches, PREG_SET_ORDER);
    $connected_clients = count($matches);
    ?>
    
    <div class="container">
        <div class="header">
            <h2>Estado de la Red - LAN</h2>
            <div class="network-badge">Punto de Acceso WiFi</div>
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
                    <div class="info-row">
                        <div class="info-label">Estado:</div>
                        <div class="info-value">
                            <span class="status-chip <?php echo $connected ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $connected ? 'ACTIVO' : 'INACTIVO'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-header">Configuración WiFi</div>
                <div class="info-content">
                    <div class="wifi-info">
                        <div class="wifi-icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <div class="wifi-details">
                            <div class="wifi-ssid"><?php echo $ssid; ?></div>
                            <div class="wifi-security">Seguridad: <?php echo $security_method; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-header">Configuración DHCP</div>
                <div class="info-content">
                    <div class="info-row">
                        <div class="info-label">Servicio DHCP:</div>
                        <div class="info-value">
                            <span class="status-chip status-active">ACTIVO</span>
                        </div>
                    </div>
                    
                    <?php if ($dhcp_start !== "No disponible" && $dhcp_end !== "No disponible"): ?>
                    <div class="dhcp-range">
                        <div class="dhcp-endpoint">
                            <div class="dhcp-label">Inicio</div>
                            <div class="dhcp-ip"><?php echo $dhcp_start; ?></div>
                        </div>
                        <div class="dhcp-arrow">
                            <i class="fas fa-long-arrow-alt-right"></i>
                        </div>
                        <div class="dhcp-endpoint">
                            <div class="dhcp-label">Fin</div>
                            <div class="dhcp-ip"><?php echo $dhcp_end; ?></div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="info-row">
                        <div class="info-label">Rango DHCP:</div>
                        <div class="info-value">No disponible</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-header">Clientes conectados</div>
                <div class="info-content">
                    <div class="info-row">
                        <div class="info-label">Número de clientes:</div>
                        <div class="info-value"><?php echo $connected_clients; ?></div>
                    </div>
                </div>
            </div>
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
