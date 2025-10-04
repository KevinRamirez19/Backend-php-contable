Write-Host "=== VERIFICACION COMPLETA DEL BACKEND CONCESIONARIO ===" -ForegroundColor Cyan
Write-Host "Probando todos los componentes..." -ForegroundColor Yellow

# 1. Verificar endpoints publicos
Write-Host "`n1. ENDPOINTS PUBLICOS:" -ForegroundColor Green
$publicEndpoints = @(
    @{Url = "/health"; Method = "GET"},
    @{Url = "/system-info"; Method = "GET"},
    @{Url = "/"; Method = "GET"}
)

foreach ($endpoint in $publicEndpoints) {
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:8000$($endpoint.Url)" -Method $endpoint.Method
        Write-Host "  ✅ $($endpoint.Method) $($endpoint.Url)" -ForegroundColor Green
    } catch {
        Write-Host "  ❌ $($endpoint.Method) $($endpoint.Url) - $($_.Exception.Message)" -ForegroundColor Red
    }
}

# 2. Probar autenticacion
Write-Host "`n2. AUTENTICACION:" -ForegroundColor Green
try {
    $loginBody = @{
        email = "admin@concesionario.com"
        password = "admin123"
    } | ConvertTo-Json

    $loginResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" -Method Post -Headers @{
        "Content-Type" = "application/json"
    } -Body $loginBody

    $token = $loginResponse.data.access_token
    Write-Host "  ✅ Login exitoso - Token obtenido" -ForegroundColor Green
} catch {
    Write-Host "  ❌ Error en login: $($_.Exception.Message)" -ForegroundColor Red
    $token = $null
}

# 3. Probar endpoints protegidos (si hay token)
if ($token) {
    Write-Host "`n3. ENDPOINTS PROTEGIDOS:" -ForegroundColor Green
    
    $protectedEndpoints = @(
        @{Url = "/api/vehiculos"; Method = "GET"; Name = "Listar vehiculos"},
        @{Url = "/api/clientes"; Method = "GET"; Name = "Listar clientes"},
        @{Url = "/api/proveedores"; Method = "GET"; Name = "Listar proveedores"},
        @{Url = "/api/ventas"; Method = "GET"; Name = "Listar ventas"},
        @{Url = "/api/compras"; Method = "GET"; Name = "Listar compras"},
        @{Url = "/api/reportes/balance-general"; Method = "GET"; Name = "Reporte balance"}
    )

    foreach ($endpoint in $protectedEndpoints) {
        try {
            $response = Invoke-RestMethod -Uri "http://localhost:8000$($endpoint.Url)" -Method $endpoint.Method -Headers @{
                "Authorization" = "Bearer $token"
            }
            Write-Host "  ✅ $($endpoint.Name)" -ForegroundColor Green
        } catch {
            Write-Host "  ❌ $($endpoint.Name) - $($_.Exception.Message)" -ForegroundColor Red
        }
    }

    # 4. Probar crear venta
    Write-Host "`n4. CREAR VENTA:" -ForegroundColor Green
    try {
        $ventaBody = @{
            cliente_id = 1
            detalles = @(
                @{
                    vehiculo_id = 1
                    cantidad = 1
                }
            )
        } | ConvertTo-Json

        $ventaResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/ventas" -Method Post -Headers @{
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
        } -Body $ventaBody

        Write-Host "  ✅ Venta creada exitosamente: $($ventaResponse.data.numero_factura)" -ForegroundColor Green
    } catch {
        Write-Host "  ❌ Error creando venta: $($_.Exception.Message)" -ForegroundColor Red
        
        # Mostrar detalles del error
        if ($_.Exception.Response) {
            $stream = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($stream)
            $responseBody = $reader.ReadToEnd()
            Write-Host "  Detalles: $responseBody" -ForegroundColor Yellow
        }
    }

    # 5. Probar logout
    Write-Host "`n5. LOGOUT:" -ForegroundColor Green
    try {
        $logoutResponse = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/logout" -Method Post -Headers @{
            "Authorization" = "Bearer $token"
        }
        Write-Host "  ✅ Logout exitoso" -ForegroundColor Green
    } catch {
        Write-Host "  ❌ Error en logout: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n=== VERIFICACION COMPLETADA ===" -ForegroundColor Cyan