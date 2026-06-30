<?php
/**
 * form.php (Versión Combinada - PHP arriba, HTML abajo)
 * Vista principal y diseño incrustado.
 */
require_once __DIR__ . '/../controllers/FormController.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utilities/SecurityUtility.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$result = ['success' => false, 'errors' => [], 'message' => '', 'old' => []];
$userModel = new UserModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new FormController();
    $result = $controller->handleSubmit($_POST);
}

$errors = $result['errors'];
$old    = $result['old'];
$temasDisponibles = $userModel->getTemas();
$csrfToken = SecurityUtility::generateCsrfToken();

if (!function_exists('old')) {
    function old(array $old, string $key): string
    {
        return SecurityUtility::escapeOutput($old[$key] ?? '');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción - iTECH</title>
    <style>
        /* ===========================================================
           Estilos integrados del formulario de inscripción iTECH
           =========================================================== */
        :root {
            --color-primary: #0d6efd;
            --color-primary-dark: #0b5ed7;
            --color-secondary: #6c757d;
            --color-success: #198754;
            --color-error: #dc3545;
            --color-bg: #f4f6f9;
            --color-card: #ffffff;
            --color-text: #212529;
            --color-border: #ced4da;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header.site-header {
            background-color: var(--color-primary);
            color: #fff;
            padding: 1.5rem 1rem;
            text-align: center;
        }

        header.site-header h1 {
            margin: 0;
            font-size: 1.6rem;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .form-card {
            background-color: var(--color-card);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            width: 100%;
            max-width: 700px;
        }

        .form-card h2 {
            margin-top: 0;
            color: var(--color-primary);
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.55rem 0.7rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 90px;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.4rem 1rem;
            border: 1px solid var(--color-border);
            border-radius: 6px;
            padding: 0.8rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .checkbox-item input {
            width: auto;
        }

        .field-error {
            color: var(--color-error);
            font-size: 0.82rem;
            margin-top: 0.25rem;
        }

        .alert {
            padding: 0.8rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: var(--color-success);
            border: 1px solid #badbcc;
        }

        .alert-error {
            background-color: #f8d7da;
            color: var(--color-error);
            border: 1px solid #f5c2c7;
        }

        .btn-submit {
            background-color: var(--color-primary);
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease-in-out;
        }

        .btn-submit:hover {
            background-color: var(--color-primary-dark);
        }

        footer.site-footer {
            background-color: #212529;
            color: #ced4da;
            text-align: center;
            padding: 1rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<header class="site-header">
    <h1>Formulario de Inscripción a Temas Tecnológicos</h1>
</header>

<main>
    <div class="form-card">
        <h2>Datos del Inscriptor</h2>

        <?php if (!empty($result['message'])): ?>
            <div class="alert alert-success"><?= SecurityUtility::escapeOutput($result['message']) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= SecurityUtility::escapeOutput($errors['general']) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['csrf'])): ?>
            <div class="alert alert-error"><?= SecurityUtility::escapeOutput($errors['csrf']) ?></div>
        <?php endif; ?>

        <form method="POST" action="form.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= SecurityUtility::escapeOutput($csrfToken) ?>">

            <div class="form-group">
                <label for="identidad">Identidad / Cédula</label>
                <input type="text" id="identidad" name="identidad" value="<?= old($old, 'identidad') ?>" required>
                <?php if (!empty($errors['identidad'])): ?>
                    <div class="field-error"><?= SecurityUtility::escapeOutput($errors['identidad']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?= old($old, 'nombre') ?>" required>
                    <?php if (!empty($errors['nombre'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['nombre']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" value="<?= old($old, 'apellido') ?>" required>
                    <?php if (!empty($errors['apellido'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['apellido']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="edad">Edad</label>
                    <input type="number" id="edad" name="edad" min="1" max="120" value="<?= old($old, 'edad') ?>" required>
                    <?php if (!empty($errors['edad'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['edad']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="sexo">Sexo</label>
                    <?php $sexoOld = $old['sexo'] ?? ''; ?>
                    <select id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="M" <?= $sexoOld === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $sexoOld === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro" <?= $sexoOld === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                    <?php if (!empty($errors['sexo'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['sexo']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pais_residencia">País de Residencia</label>
                    <input type="text" id="pais_residencia" name="pais_residencia" value="<?= old($old, 'pais_residencia') ?>" required>
                    <?php if (!empty($errors['pais_residencia'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['pais_residencia']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="nacionalidad">Nacionalidad</label>
                    <input type="text" id="nacionalidad" name="nacionalidad" value="<?= old($old, 'nacionalidad') ?>" required>
                    <?php if (!empty($errors['nacionalidad'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['nacionalidad']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" value="<?= old($old, 'correo') ?>" required>
                    <?php if (!empty($errors['correo'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['correo']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="celular">Celular</label>
                    <input type="tel" id="celular" name="celular" value="<?= old($old, 'celular') ?>" required>
                    <?php if (!empty($errors['celular'])): ?>
                        <div class="field-error"><?= SecurityUtility::escapeOutput($errors['celular']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Temas Tecnológicos de Interés</label>
                <div class="checkbox-group">
                    <?php
                    $temasOld = array_map('intval', $old['temas'] ?? []);
                    foreach ($temasDisponibles as $tema):
                        $checked = in_array((int) $tema['id_tema'], $temasOld, true) ? 'checked' : '';
                    ?>
                        <div class="checkbox-item">
                            <input
                                type="checkbox"
                                id="tema_<?= (int) $tema['id_tema'] ?>"
                                name="temas[]"
                                value="<?= (int) $tema['id_tema'] ?>"
                                <?= $checked ?>
                            >
                            <label for="tema_<?= (int) $tema['id_tema'] ?>" style="font-weight:400; margin:0;">
                                <?= SecurityUtility::escapeOutput($tema['nombre']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($errors['temas'])): ?>
                    <div class="field-error"><?= SecurityUtility::escapeOutput($errors['temas']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" maxlength="1000"><?= old($old, 'observaciones') ?></textarea>
                <?php if (!empty($errors['observaciones'])): ?>
                    <div class="field-error"><?= SecurityUtility::escapeOutput($errors['observaciones']) ?></div>
                <?php endif; ?>
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