<?php
/**
 * form.php
 * Vista principal: formulario de inscripción a temas tecnológicos iTECH.
 * Este archivo actúa como punto de entrada (front controller simplificado):
 * recibe el POST, lo delega al FormController y luego renderiza el resultado.
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

/**
 * Helper local para reimprimir valores anteriores de forma segura (escapados).
 */
function old(array $old, string $key): string
{
    return SecurityUtility::escapeOutput($old[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción - iTECH</title>
    <link rel="stylesheet" href="style.css">
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

            <!-- Identidad -->
            <div class="form-group">
                <label for="identidad">Identidad / Cédula</label>
                <input type="text" id="identidad" name="identidad" value="<?= old($old, 'identidad') ?>" required>
                <?php if (!empty($errors['identidad'])): ?>
                    <div class="field-error"><?= SecurityUtility::escapeOutput($errors['identidad']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Nombre y apellido -->
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

            <!-- Edad y sexo -->
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

            <!-- País de residencia y nacionalidad -->
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

            <!-- Correo y celular -->
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

            <!-- Temas tecnológicos -->
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

            <!-- Observaciones -->
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