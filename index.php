<?php
/**
 * index.php
 * Punto de entrada principal del proyecto Parcial — iTECH.
 *
 * Enrutamiento por parámetro GET ?action=:
 *   (vacío / form)  → formulario de inscripción
 *   success         → confirmación de registro exitoso
 *   report          → reporte de inscriptores
 *
 * Este archivo carga los controladores/modelos necesarios según la
 * acción y pasa las variables a las vistas.
 */

// Constante de acceso: evita que las vistas se carguen directamente.
define('ITECH_APP', true);

// ---- Iniciar sesión (necesaria para CSRF) ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Autocargar clases según namespace sencillo ----
require_once __DIR__ . '/utilities/SecurityUtility.php';
require_once __DIR__ . '/utilities/ValidationUtility.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/controllers/FormController.php';

// ---- Determinar acción ----
$action = SecurityUtility::sanitizeInput($_GET['action'] ?? 'form');

// ---- Variables comunes ---- 
$userModel = new UserModel();
$errors    = [];
$old       = [];
$csrf      = SecurityUtility::generateCsrfToken();

// ================================================================
// ACCIÓN: form  (GET → mostrar formulario / POST → procesar envío)
// ================================================================
if ($action === 'form') {

    $paises = $userModel->getPaises();
    $areas  = $userModel->getAreasInteres();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new FormController();
        $result = $controller->handleSubmit($_POST);

        if ($result['success']) {
            // Redirigir a success con el ID del nuevo inscriptor
            $id = (int) ($result['id_inscriptor'] ?? 0);
            header("Location: index.php?action=success&id={$id}");
            exit;
        }

        // Hubo errores: devolver al formulario con datos anteriores
        $errors = $result['errors'];
        $old    = $result['old'];
        $csrf   = SecurityUtility::generateCsrfToken();
    }

    require __DIR__ . '/views/form.php';
    exit;
}

// ================================================================
// ACCIÓN: success
// ================================================================
if ($action === 'success') {
    $idInscriptor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
    require __DIR__ . '/views/success.php';
    exit;
}

// ================================================================
// ACCIÓN: report
// ================================================================
if ($action === 'report') {
    require_once __DIR__ . '/controllers/ReportController.php';
    $controller = new ReportController();

    // Descargar Excel directamente al navegador (no se guarda en el proyecto)
    if (isset($_GET['exportar']) && $_GET['exportar'] === '1') {
        try {
            $controller->downloadExcel(); // envía headers + archivo y termina con exit
        } catch (Throwable $e) {
            $exportMsg = 'Error al exportar: ' . $e->getMessage();
        }
    }

    $data      = $controller->buildReport();
    $exportMsg = $exportMsg ?? null;
    require __DIR__ . '/views/report.php';
    exit;
}

// ================================================================
// ACCIÓN no reconocida → redirigir al formulario
// ================================================================
header('Location: index.php?action=form');
exit;