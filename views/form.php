<?php
/**
 * views/form.php (Versión Combinada Completa)
 * Vista del formulario de inscripción a iTECH con estilos CSS incrustados.
 * * LÓGICA DE NEGOCIO (PHP ARRIBA)
 */

if (!defined('ITECH_APP')) {
    header('Location: ../index.php');
    exit;
}

/**
 * Devuelve el valor anterior escapado, o '' si no existe.
 */
function old(array $old, string $key): string
{
    return htmlspecialchars($old[$key] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Renderiza un mensaje de error de campo si existe.
 */
function fieldError(array $errors, string $key): string
{
    if (empty($errors[$key])) {
        return '';
    }
    $msg = htmlspecialchars($errors[$key], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return "<div class=\"field-error\">⚠ {$msg}</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inscripción — iTECH</title>
    <style>
        /* ============================================================
           Estilos globales integrados del proyecto iTECH 
           ============================================================ */

        /* ---- Variables de diseño ---- */
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

        /* ---- Reset básico ---- */
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

        /* ---- Header ---- */
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

        /* ---- Main ---- */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2.5rem 1rem;
        }

        /* ---- Tarjeta del formulario ---- */
        .form-card {
            background-color: var(--color-card);
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 2.2rem 2rem;
            width: 100%;
            max-width: 740px;
        }

        .form-card h2 {
            color: var(--color-primary);
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
        }

        /* ---- Grupos de campo ---- */
        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
            font-size: 0.92rem;
        }

        /* Texto de ayuda junto al label */
        .hint {
            font-weight: 400;
            color: var(--color-muted);
            font-size: 0.78rem;
        }

        /* Asterisco de campo obligatorio */
        .req {
            color: var(--color-error);
            margin-left: 2px;
        }

        /* Inputs, selects y textarea */
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            font-size: 0.93rem;
            background-color: #fdfdfd;
            transition: border-color 0.15s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }

        /* Select múltiple — áreas de interés */
        .select-multiple {
            min-height: 150px;
            padding: 0.4rem;
        }

        .select-multiple option {
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
        }

        .select-multiple option:checked {
            background: var(--color-primary);
            color: #ffffff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }

        /* Fila de dos columnas */
        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        /* ---- Errores por campo ---- */
        .field-error {
            color: var(--color-error);
            font-size: 0.80rem;
            margin-top: 0.25rem;
        }

        /* ---- Alertas globales ---- */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.2rem;
            font-size: 0.92rem;
        }

        .alert-success {
            background-color: var(--color-success-bg);
            color: var(--color-success);
            border: 1px solid #badbcc;
        }

        .alert-error {
            background-color: var(--color-error-bg);
            color: var(--color-error);
            border: 1px solid #f5c2c7;
        }

        /* ---- Botón principal ---- */
        .btn-submit {
            display: block;
            width: 100%;
            background-color: var(--color-primary);
            color: #ffffff;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--color-primary-dark);
        }

        /* ---- Footer ---- */
        footer.site-footer {
            background-color: #212529;
            color: #ced4da;
            text-align: center;
            padding: 1rem;
            font-size: 0.83rem;
        }

        /* ---- Responsive ---- */
        @media (max-width: 560px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-card {
                padding: 1.4rem 1rem;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <h1>iTECH — Formulario de Inscripción</h1>
</header>

<main>
    <div class="form-card">
        <h2>Datos del Inscriptor</h2>

        <?php if (!empty($errors['csrf'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errors['csrf'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=form" novalidate>

            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre <span class="req">*</span></label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?= old($old, 'nombre') ?>"
                           placeholder="Ej: Juan Carlos"
                           required>
                    <?= fieldError($errors, 'nombre') ?>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido <span class="req">*</span></label>
                    <input type="text" id="apellido" name="apellido"
                           value="<?= old($old, 'apellido') ?>"
                           placeholder="Ej: Pérez González"
                           required>
                    <?= fieldError($errors, 'apellido') ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edad">Edad <span class="req">*</span></label>
                    <input type="number" id="edad" name="edad"
                           value="<?= old($old, 'edad') ?>"
                           min="1" max="120" placeholder="Ej: 25"
                           required>
                    <?= fieldError($errors, 'edad') ?>
                </div>

                <div class="form-group">
                    <label for="sexo">Sexo <span class="req">*</span></label>
                    <?php $sexoOld = $old['sexo'] ?? ''; ?>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="Masculino" <?= $sexoOld === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Femenino"  <?= $sexoOld === 'Femenino'  ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro"      <?= $sexoOld === 'Otro'      ? 'selected' : '' ?>>Otro</option>
                    </select>
                    <?= fieldError($errors, 'sexo') ?>
                </div>
            </div>

            <div class="form-group">
                <label for="pais_residencia_id">País de Residencia<span class="req">*</span></label>
                <select id="pais_residencia_id" name="pais_residencia_id" required>
                    <option value="">— Seleccione un país —</option>
                    <?php foreach ($paises as $pais): ?>
                        <option
                            value="<?= (int) $pais['id'] ?>"
                            <?= (int) ($old['pais_residencia_id'] ?? 0) === (int) $pais['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pais['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= fieldError($errors, 'pais_residencia_id') ?>
            </div>

            <div class="form-group">
    <label for="nacionalidad_id">
        Nacionalidad <span class="req">*</span>
    </label>

    <select id="nacionalidad_id" name="nacionalidad_id" required>
        <option value="">— Seleccione una nacionalidad —</option>

        <?php foreach ($paises as $pais): ?>
            <option
                value="<?= (int)$pais['id'] ?>"
                <?= (int)($old['nacionalidad_id'] ?? 0) === (int)$pais['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($pais['nombre'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?= fieldError($errors, 'nacionalidad_id') ?>
</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="correo">Correo Electrónico <span class="req">*</span></label>
                    <input type="email" id="correo" name="correo"
                           value="<?= old($old, 'correo') ?>"
                           placeholder="usuario@correo.com"
                           required>
                    <?= fieldError($errors, 'correo') ?>
                </div>

                <div class="form-group">
                    <label for="celular">Celular <span class="req">*</span></label>
                    <input type="tel" id="celular" name="celular"
                           value="<?= old($old, 'celular') ?>"
                           placeholder="Ej: +507 6000-0000"
                           required>
                    <?= fieldError($errors, 'celular') ?>
                </div>
            </div>

            <div class="form-group">
                <label for="areas">
                    Áreas de Interés Tecnológico <span class="req">*</span>
                    <span class="hint">(Mantén Ctrl / Cmd para seleccionar varias)</span>
                </label>
                <?php
                $areasOld = array_map('intval', $old['areas'] ?? []);
                ?>
                <select id="areas" name="areas[]" multiple required class="select-multiple">
                    <?php foreach ($areas as $area): ?>
                        <option
                            value="<?= (int) $area['id'] ?>"
                            <?= in_array((int) $area['id'], $areasOld, true) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($area['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= fieldError($errors, 'areas') ?>
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones"
                          maxlength="1000"
                          placeholder="Comentarios adicionales (opcional)..."><?= old($old, 'observaciones') ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Enviar Inscripción</button>

        </form>
    </div>
</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> iTECH. All rights reserved.
</footer>

</body>
</html>