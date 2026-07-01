<?php
/**
 * views/success.php (Versión Combinada Completa)
 * Vista de confirmación de inscripción exitosa con estilos CSS incrustados.
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
    <style>
        /* ============================================================
           Estilos globales integrados (Procedentes de Style.css)
           ============================================================ */
        :root {
            --color-primary:       #0d6efd;
            --color-primary-dark:  #0b5ed7;
            --color-success:       #198754;
            --color-success-bg:    #d1e7dd;
            --color-error:         #dc3545;
            --color-error-bg:      #f8d7da;
            --color-warning:       #664d03;
            --color-warning-bg:    #fff3cd;
            --color-bg:            #f4f6f9;
            --color-card:          #ffffff;
            --color-text:          #212529;
            --color-muted:         #6c757d;
            --color-border:        #ced4da;
            --radius:              8px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            line-height: 1.6;
        }

        header.site-header {
            background-color: var(--color-primary);
            color: #ffffff;
            padding: 1.2rem 2rem;
            text-align: center;
        }

        header.site-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        footer.site-footer {
            background-color: #212529;
            color: #ced4da;
            text-align: center;
            padding: 1rem;
            font-size: 0.83rem;
        }

        /* ============================================================
           Estilos específicos de la vista de éxito 
           ============================================================ */
        .success-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            padding: 3rem 1rem;
        }

        .success-card {
            background: var(--color-card);
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
            color: var(--color-success);
        }

        .success-card h2 {
            color: var(--color-success);
            margin: 0 0 0.5rem;
            font-size: 1.8rem;
        }

        .success-card p {
            color: var(--color-muted);
            margin: 0 0 2rem;
            font-size: 1rem;
        }

        .success-id {
            display: inline-block;
            background: var(--color-success-bg);
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
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .btn:hover { 
            opacity: 0.88; 
        }

        .btn-primary {
            background-color: var(--color-primary);
            color: #ffffff;
        }

        .btn-secondary {
            background-color: var(--color-secondary);
            color: #1287d4;
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