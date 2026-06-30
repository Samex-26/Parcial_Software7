<?php
/**
 * index.php
 * Punto de entrada principal del proyecto Parcial.
 */

// Acción por defecto: formulario
$action = $_GET['action'] ?? 'form';

switch ($action) {
    case 'form':
        require_once __DIR__ . '/views/form.php';
        break;

    case 'report':
        require_once __DIR__ . '/controllers/ReportController.php';
        $controller = new ReportController();
        $controller->showReport();
        break;

    case 'export':
        require_once __DIR__ . '/controllers/ReportController.php';
        $controller = new ReportController();
        $file = $controller->exportToExcel();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'file' => "reports/{$file}"]);
        break;

    default:
        http_response_code(404);
        echo 'Ruta no encontrada.';
}
