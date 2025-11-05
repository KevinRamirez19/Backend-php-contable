<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro Diario</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .detalle { margin-left: 20px; }
    </style>
</head>
<body>
    <h1>Libro Diario</h1>

    @foreach($asientos as $asiento)
        <p><strong>Fecha:</strong> {{ $asiento['fecha'] ?? 'N/A' }}</p>
        <p><strong>Descripción:</strong> {{ $asiento['descripcion'] ?? '' }}</p>
        <table>
            <thead>
                <tr>
                    <th>Código Cuenta</th>
                    <th>Nombre Cuenta</th>
                    <th>Debe</th>
                    <th>Haber</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asiento['partidas'] as $p)
                    <tr>
                        <td>{{ $p['cuenta_codigo'] }}</td>
                        <td>{{ $p['cuenta_nombre'] }}</td>
                        <td>{{ number_format($p['debe'], 2, ',', '.') }}</td>
                        <td>{{ number_format($p['haber'], 2, ',', '.') }}</td>
                        <td>{{ $p['descripcion'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
    @endforeach
</body>
</html>
