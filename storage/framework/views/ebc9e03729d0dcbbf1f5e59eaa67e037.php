<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro Diario</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        h3 {
            margin-bottom: 5px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .totales {
            font-weight: bold;
            background-color: #eee;
        }
        .asiento {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <h1>Libro Diario</h1>

    <?php $__currentLoopData = $asientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asiento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="asiento">
            <h3>Fecha: <?php echo e($asiento['fecha'] ?? 'N/A'); ?></h3>
            <p><strong>Descripción:</strong> <?php echo e($asiento['descripcion'] ?? ''); ?></p>

            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre de Cuenta</th>
                        <th>Tipo de Cuenta</th>
                        <th>Debe</th>
                        <th>Haber</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalDebe = 0;
                        $totalHaber = 0;
                    ?>

                    <?php $__currentLoopData = $asiento['partidas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $totalDebe += $p['debe'];
                            $totalHaber += $p['haber'];
                        ?>
                        <tr>
                            <td><?php echo e($p['cuenta_codigo']); ?></td>
                            <td><?php echo e($p['cuenta_nombre']); ?></td>
                            <td><?php echo e($p['tipo_cuenta']); ?></td>
                            <td><?php echo e(number_format($p['debe'], 2, ',', '.')); ?></td>
                            <td><?php echo e(number_format($p['haber'], 2, ',', '.')); ?></td>
                            <td><?php echo e($p['descripcion']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <tr class="totales">
                        <td colspan="3" align="right">Totales:</td>
                        <td><?php echo e(number_format($totalDebe, 2, ',', '.')); ?></td>
                        <td><?php echo e(number_format($totalHaber, 2, ',', '.')); ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH C:\Users\ASUS\Downloads\Backend-php-contable\resources\views/reportes/libro_diario.blade.php ENDPATH**/ ?>