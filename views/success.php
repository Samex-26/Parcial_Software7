<?php
/**
 * views/success.php
 * Vista de confirmación de inscripción exitosa.
 * Recibe (desde index.php) la variable $idInscriptor
 * opcional para personalizar el mensaje.
 *
 * Botones:
 *   ① Regresar al formulario  → index.php?action=form
 *   ② Ir al área de reportes  → index.php?action=report
 */

// Proteger acceso directo
if (!defined('ITECH_APP')) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso — iTECH</title>
    <link rel="stylesheet" href="Style.css">
    <style>
        /* ---- Estilos específicos de la vista de éxito ---- */
        .success-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 3rem 1rem;
        }

        .success-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 3rem 2.5rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            font-size: 4rem;
            line-height: 1;
            margin-bottom: 1rem;
            color: #198754;
        }

        .success-card h2 {
            color: #198754;
            margin: 0 0 0.5rem;
            font-size: 1.8rem;
        }

        .success-card p {
            color: #6c757d;
            margin: 0 0 2rem;
            font-size: 1rem;
        }

        .success-id {
            display: inline-block;
            background: #d1e7dd;
            color: #0f5132;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 0.7rem 1.4rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .btn:hover { opacity: 0.88; }

        .btn-primary {
            background-color: #0d6efd;
            color: #ffffff;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #ffffff;
        }
    </style>
</head>
<body>

<header class="site-header">
    <h1>iTECH — Sistema de Inscripciones</h1>
</header>

<main class="success-wrapper">
    <div class="success-card">

        <div class="success-icon">✔</div>

        <h2>¡Registro Exitoso!</h2>

        <p>Tu inscripción ha sido guardada correctamente en el sistema.</p>

        <?php if (!empty($idInscriptor)): ?>
            <div class="success-id">
                ID de inscripción: #<?= (int) $idInscriptor ?>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="index.php?action=form" class="btn btn-primary">
                ← Nuevo Registro
            </a>
            <a href="index.php?action=report" class="btn btn-secondary">
                Ver Reportes →
            </a>
        </div>

    </div>
</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> iTECH. All rights reserved.
</footer>

</body>
</html>