<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $venta->numero_factura }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 30px; font-size: 12px; color: #333; }
        h1, h2, h3 { text-align: center; margin: 0; }
        h1 { font-size: 20px; margin-bottom: 5px; }
        h2 { font-size: 16px; margin-bottom: 15px; }
        h3 { font-size: 14px; margin-top: 20px; margin-bottom: 5px; }
        p { margin: 2px 0; }
        .section { margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f8f8f8; }
        .totales { text-align: right; margin-top: 10px; font-size: 13px; }
        .firma { margin-top: 40px; text-align: center; font-size: 11px; color: #555; }
        .cufe { word-break: break-all; font-size: 10px; }
        .label { font-weight: 600; color: #222; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;

        // Forzar locale español
        Carbon::setLocale('es');
        // Intentar setlocale en el servidor (varía por SO)
        setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain', 'es_CO.UTF-8', 'es_CO', 'es');

        // Formatear fecha (ej: "1 de noviembre de 2025")
        $fechaFormateada = null;
        if (!empty($venta->fecha_venta)) {
            try {
                $fechaFormateada = Carbon::parse($venta->fecha_venta)->translatedFormat('j \\d\\e F \\d\\e Y');
            } catch (\Exception $e) {
                $fechaFormateada = $venta->fecha_venta;
            }
        }

        // Obtener datos del cliente
        $cliente = $venta->cliente ?? null;
        $numeroDoc = $cliente->numero_documento ?? null;

        // Determinar tipo de identificación:
        // 1) Preferir campo explícito tipo_identificacion o tipo_documento
        // 2) Si no existe, inferir por longitud del número (heurística simple)
        $tipoIdentificacion = null;
        if (!empty($cliente->tipo_identificacion)) {
            $tipoIdentificacion = $cliente->tipo_identificacion;
        } elseif (!empty($cliente->tipo_documento)) {
            $tipoIdentificacion = $cliente->tipo_documento;
        } elseif (!empty($numeroDoc)) {
            // Inferir: si longitud >= 9 -> NIT, si < 9 -> C.C.
            $clean = preg_replace('/\D+/', '', $numeroDoc);
            $tipoIdentificacion = (strlen($clean) >= 9) ? 'NIT' : 'C.C.';
        } else {
            $tipoIdentificacion = null;
        }

        // Mostrar texto amigable
        $identText = $tipoIdentificacion
            ? ($tipoIdentificacion . ': ' . ($numeroDoc ?? 'No registrado'))
            : ($numeroDoc ? $numeroDoc : 'No registrado');

        // Helper formateo de moneda (sin símbolos de biblioteca externa)
        $fmt = function($n){
            return number_format((float)$n, 0, ',', '.');
        };
    @endphp

    <h1>Factura Electrónica</h1>
    <h2>#{{ $venta->numero_factura }}</h2>

    <div class="section">
        <p><span class="label">Fecha:</span> {{ $fechaFormateada ?? 'No registrada' }}</p>
        <p><span class="label">Estado DIAN:</span> {{ $venta->estado_dian ?? 'Generada' }}</p>
    </div>

    <div class="section">
        <h3>Emisor</h3>
        <p><span class="label">Razón social:</span> AUTOMORATA S.A.S.</p>
        <p><span class="label">Identificación:</span> NIT 900123456</p>
        <p><span class="label">Dirección:</span> Calle 100 #15-30, BOGOTÁ D.C.</p>
    </div>

    <div class="section">
        <h3>Cliente</h3>
        <p><span class="label">Nombre:</span> {{ $cliente->nombre ?? 'No registrado' }}</p>
        <p><span class="label">Identificación:</span> {{ $identText }}</p>
        <p><span class="label">Dirección:</span> {{ $cliente->direccion ?? 'No registrada' }}</p>
    </div>

    <div class="section">
        <h3>Detalle de la venta</h3>
        <table>
            <thead>
                <tr>
                    <th>Vehículo</th>
                    <th style="width:70px; text-align:center">Cantidad</th>
                    <th style="width:120px; text-align:right">Precio unitario</th>
                    <th style="width:120px; text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr>
                    <td style="font-size:11px">{{ $detalle->vehiculo->marca }} {{ $detalle->vehiculo->modelo }} {{ $detalle->vehiculo->año }} - {{ $detalle->vehiculo->color ?? '' }}</td>
                    <td style="text-align:center">{{ $detalle->cantidad }}</td>
                    <td style="text-align:right">${{ $fmt($detalle->precio_unitario) }}</td>
                    <td style="text-align:right">${{ $fmt($detalle->subtotal) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totales">
        <p>Subtotal: <strong>${{ $fmt($venta->subtotal) }}</strong></p>
        <p>IVA (19%): <strong>${{ $fmt($venta->iva) }}</strong></p>
        <p style="font-size:15px"><strong>Total: ${{ $fmt($venta->total) }}</strong></p>
    </div>

    <div class="firma">
        <p><strong>Firma Digital:</strong> AUTOMORATA S.A.S.</p>
        <p><strong>CUFE:</strong></p>
        <p class="cufe">{{ $venta->cufe ?? 'No generado' }}</p>
    </div>
</body>
</html>
