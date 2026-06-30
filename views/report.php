<?php
/**
 * report.php
 * Vista del reporte de inscriptores con indicadores de integridad.
 * Espera la variable $data inyectada desde ReportController.
 * Columnas alineadas al esquema real: nombre, apellido, edad, sexo,
 * correo, celular, pais_residencia, nacionalidad, temas.
 */
require_once __DIR__ . '/../utilities/ValidationUtility.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Inscriptores - Parcial ITECH</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f4f6f8;
        margin: 0;
        padding: 30px;
        color: #2c3e50;
    }
    h1 {
        text-align: center;
        color: #1a3c6e;
        margin-bottom: 5px;
    }
    .subtitle {
        text-align: center;
        color: #666;
        margin-bottom: 25px;
    }
    .actions {
        text-align: right;
        margin-bottom: 15px;
    }
    .btn-export {
        background: #1e8449;
        color: #fff;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
    }
    .btn-export:hover { background: #196f3d; }

    table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 8px;
        overflow: hidden;
    }
    thead { background: #1a3c6e; color: #fff; }
    th, td {
        padding: 12px 14px;
        text-align: left;
        font-size: 14px;
        border-bottom: 1px solid #eaeaea;
    }
    tbody tr:hover { background: #f1f5fb; }

    .status-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }
    .green { background-color: #27ae60; }
    .red   { background-color: #e74c3c; }

    .status-label { font-size: 13px; font-weight: 600; }
    .status-label.green-text { color: #1e8449; }
    .status-label.red-text   { color: #c0392b; }

    .empty {
        text-align: center;
        padding: 30px;
        color: #888;
    }
</style>
</head>
<body>

    <h1>Reporte de Inscriptores</h1>
    <p class="subtitle">Base de datos: parcial_itech</p>

    <div class="actions">
        <a class="btn-export" href="?export=excel">⬇ Exportar a Excel</a>
    </div>

    <?php if (empty($data)): ?>
        <div class="empty">No hay inscriptores registrados.</div>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Correo</th>
                <th>Celular</th>
                <th>País Residencia</th>
                <th>Nacionalidad</th>
                <th>Temas Tecnológicos</th>
                <th>Integridad</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td><?= ValidationUtility::sanitize((string)$row['id']) ?></td>
                <td><?= ValidationUtility::sanitize($row['nombre']) ?></td>
                <td><?= ValidationUtility::sanitize($row['apellido']) ?></td>
                <td><?= ValidationUtility::sanitize((string)$row['edad']) ?></td>
                <td><?= ValidationUtility::sanitize($row['sexo']) ?></td>
                <td><?= ValidationUtility::sanitize($row['correo']) ?></td>
                <td><?= ValidationUtility::sanitize($row['celular']) ?></td>
                <td><?= ValidationUtility::sanitize($row['pais_residencia'] ?? 'N/A') ?></td>
                <td><?= ValidationUtility::sanitize($row['nacionalidad'] ?? 'N/A') ?></td>
                <td><?= ValidationUtility::sanitize($row['temas'] ?? 'Sin temas') ?></td>
                <td>
                    <?php if ($row['audit_status'] === 'green'): ?>
                        <span class="status-dot green"></span>
                        <span class="status-label green-text">Validado</span>
                    <?php else: ?>
                        <span class="status-dot red"></span>
                        <span class="status-label red-text">Comprometido</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

</body>
</html>