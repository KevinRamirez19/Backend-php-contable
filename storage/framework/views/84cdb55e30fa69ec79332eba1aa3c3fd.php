<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Contable - Concesionario de Vehículos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #667eea;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.2rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        
        .feature {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .feature h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .feature p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .status {
            background: #e8f5e8;
            color: #2d5016;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
        }
        
        .tech-stack {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        
        .tech {
            background: #667eea;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .api-info {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: left;
        }
        
        .api-info h3 {
            color: #1976d2;
            margin-bottom: 10px;
        }
        
        .endpoint {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin: 5px 0;
            font-size: 0.9rem;
        }
        
        .footer {
            margin-top: 30px;
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🚗</div>
        <h1>Bienvenido al Sistema Contable</h1>
        <p class="subtitle">
            Sistema integral de gestión para concesionario de vehículos con 
            facturación electrónica DIAN y contabilidad automatizada.
        </p>
        
        <div class="status">
            ✅ <strong>Sistema funcionando correctamente</strong><br>
            Backend Laravel 10 + MySQL + Docker
        </div>
        
        <div class="features">
            <div class="feature">
                <h3>📦 Inventario</h3>
                <p>Gestión completa de vehículos, stock y proveedores</p>
            </div>
            <div class="feature">
                <h3>💰 Ventas</h3>
                <p>Proceso de ventas con facturación electrónica DIAN</p>
            </div>
            <div class="feature">
                <h3>📊 Contabilidad</h3>
                <p>Libro contable automático y reportes financieros</p>
            </div>
        </div>
        
        <div class="tech-stack">
            <span class="tech">Laravel 10</span>
            <span class="tech">PHP 8.2</span>
            <span class="tech">MySQL 8</span>
            <span class="tech">Docker</span>
            <span class="tech">JWT Auth</span>
            <span class="tech">DIAN API</span>
        </div>
        
        <div class="api-info">
            <h3>📡 API REST Disponible</h3>
            <p>El backend está listo para recibir peticiones:</p>
            <div class="endpoint">POST /api/auth/login</div>
            <div class="endpoint">GET /api/vehiculos</div>
            <div class="endpoint">POST /api/ventas</div>
            <div class="endpoint">GET /api/reportes/balance</div>
        </div>
        
        <div class="footer">
            &copy; 2024 Concesionario Vehículos - Sistema desarrollado con Laravel
        </div>
    </div>
</body>
</html><?php /**PATH /var/www/resources/views/welcome.blade.php ENDPATH**/ ?>