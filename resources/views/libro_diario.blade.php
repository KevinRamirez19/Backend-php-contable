<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>Libro Diario</h2>
    @foreach($asientos as $asiento)
        <h4>{{ $asiento['fecha'] }} - {{ $asiento['descripcion'] }}</h4>
        <table>
            <thead>
                <tr>
                    <th>Código Cuenta</th>
                    <th>Nombre Cuenta</th>
                    <th>Debe</th>
                    <th>Haber</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asiento['partidas'] as $p)
                <tr>
                    <td>{{ $p['cuenta_codigo'] }}</td>
                    <td>{{ $p['cuenta_nombre'] }}</td>
                    <td>{{ number_format($p['debe'],2) }}</td>
                    <td>{{ number_format($p['haber'],2) }}</td>
                    <td>{{ $p['descripcion'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>
