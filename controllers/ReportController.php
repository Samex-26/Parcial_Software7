<?php
/**
 * ReportController.php
 * Controlador encargado de generar el reporte de inscriptores
 * y disparar la exportación a Excel.
 */

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../utilities/ValidationUtility.php';
require_once __DIR__ . '/../reports/ExcelExporter.php';

class ReportController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->db->ensureForeignKeys();
    }

    /**
     * Obtiene todos los inscriptores con país y temas (concatenados por coma).
     */
    public function getInscriptores(): array
{
    $sql = "
        SELECT 
            i.id,
            i.nombre,
            i.apellido,
            i.edad,
            i.sexo,
            i.correo,
            i.celular,
            i.observaciones,
            i.fecha_registro,
            pr.nombre AS pais_residencia,
            na.nombre AS nacionalidad,
            GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS temas
        FROM inscriptores i
        LEFT JOIN paises pr ON pr.id = i.pais_residencia_id
        LEFT JOIN paises na ON na.id = i.nacionalidad_id
        LEFT JOIN inscriptor_temas it ON it.inscriptor_id = i.id
        LEFT JOIN areas_interes a ON a.id = it.area_interes_id
        GROUP BY i.id, i.nombre, i.apellido, i.edad, i.sexo, i.correo,
                 i.celular, i.observaciones, i.fecha_registro, pr.nombre, na.nombre
        ORDER BY i.id ASC
    ";

    $stmt = $this->db->prepare($sql);
    if ($stmt === false) {
        return [];
    }

    return $stmt->fetchAll();
}

    /**
     * Construye el reporte agregando el estado de auditoría (verde/rojo) a cada fila.
     */
    public function buildReport(): array
    {
        $inscriptores = $this->getInscriptores();

        foreach ($inscriptores as &$row) {
            $row['audit_status'] = ValidationUtility::auditStatus($row);
            $row['signature']    = ValidationUtility::signRecord($row);
        }
        unset($row);

        return $inscriptores;
    }

    /**
     * Punto de entrada para mostrar el reporte en pantalla.
     */
    public function showReport(): void
    {
        $data = $this->buildReport();
        require __DIR__ . '/../views/report.php';
    }

    /**
     * Exporta el reporte actual a un archivo .xlsx dentro de reports/.
     */
    public function exportToExcel(): string
    {
        $data = $this->buildReport();
        $exporter = new ExcelExporter();

        $headers = ['ID', 'Nombre', 'Apellido', 'Edad', 'Sexo', 'Correo', 'Celular',
            'País Residencia', 'Nacionalidad', 'Temas', 'Auditoría'];

         $rows = array_map(function ($r) {
     return [
        $r['id'],
        $r['nombre'],
        $r['apellido'],
        $r['edad'],
        $r['sexo'],
        $r['correo'],
        $r['celular'],
        $r['pais_residencia'] ?? '',
        $r['nacionalidad'] ?? '',
        $r['temas'] ?? '',
        $r['audit_status'] === 'green' ? 'Válido' : 'Comprometido',
    ];
}, $data);

        $filename = 'reporte_inscriptores_' . date('Ymd_His') . '.xlsx';
        $path = __DIR__ . '/../reports/' . $filename;

        $exporter->export($headers, $rows, $path);

        return $filename;
    }

    /**
     * Genera el reporte en un archivo .xlsx temporal (fuera del proyecto)
     * y lo envía directamente al navegador como descarga (Content-Disposition:
     * attachment). El navegador la guarda en la carpeta de Descargas del
     * usuario; el archivo NUNCA queda almacenado dentro del proyecto.
     */
    public function downloadExcel(): void
    {
        $data = $this->buildReport();
        $exporter = new ExcelExporter();

        $headers = ['ID', 'Nombre', 'Apellido', 'Edad', 'Sexo', 'Correo', 'Celular',
            'País Residencia', 'Nacionalidad', 'Temas', 'Auditoría'];

        $rows = array_map(function ($r) {
            return [
                $r['id'],
                $r['nombre'],
                $r['apellido'],
                $r['edad'],
                $r['sexo'],
                $r['correo'],
                $r['celular'],
                $r['pais_residencia'] ?? '',
                $r['nacionalidad'] ?? '',
                $r['temas'] ?? '',
                $r['audit_status'] === 'green' ? 'Válido' : 'Comprometido',
            ];
        }, $data);

        $filename = 'reporte_inscriptores_' . date('Ymd_His') . '.xlsx';

        // Ruta temporal FUERA del proyecto (carpeta temp del sistema/servidor)
        $tempPath = tempnam(sys_get_temp_dir(), 'itech_xlsx_');
        if ($tempPath === false) {
            throw new Exception('No se pudo crear el archivo temporal para exportar.');
        }
        // tempnam() no agrega extensión; ZipArchive/Excel esperan .xlsx
        $tempXlsx = $tempPath . '.xlsx';
        rename($tempPath, $tempXlsx);

        $ok = $exporter->export($headers, $rows, $tempXlsx);

        if (!$ok || !file_exists($tempXlsx)) {
            throw new Exception('No se pudo generar el archivo Excel.');
        }

        // Limpiar cualquier salida previa (evita corromper el binario del xlsx)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempXlsx));
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        readfile($tempXlsx);
        unlink($tempXlsx);
        exit;
    }
}

// ---- Manejo de la petición (cuando se accede directamente vía router) ----
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $controller = new ReportController();

    if (isset($_GET['export']) && $_GET['export'] === 'excel') {
        $controller->downloadExcel();
        exit;
    }

    $controller->showReport();
}