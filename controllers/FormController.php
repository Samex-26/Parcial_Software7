<?php
/**
 * FormController.php
 * Controlador encargado de procesar el envío del formulario de inscripción.
 * Flujo: CSRF -> Sanitización -> Validación -> Inserción -> Respuesta a la vista.
 */

require_once __DIR__ . '/../utilities/SecurityUtility.php';
require_once __DIR__ . '/../utilities/ValidationUtility.php';
require_once __DIR__ . '/../models/UserModel.php';

class FormController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Procesa la petición POST del formulario.
     *
     * @return array{success: bool, errors: array, message: string, old: array}
     */
    public function handleSubmit(array $postData): array
    {
        // 1. Validar token CSRF
        if (!SecurityUtility::validateCsrfToken($postData['csrf_token'] ?? null)) {
            return [
                'success' => false,
                'errors'  => ['csrf' => 'Token de seguridad inválido. Recargue la página e intente de nuevo.'],
                'message' => '',
                'old'     => [],
            ];
        }

        // 2. Sanitizar entradas (excepto el arreglo de temas, que se sanitiza aparte como enteros)
        $clean = SecurityUtility::sanitizeArray($postData);

        // Los temas deben tratarse como enteros, no como texto sanitizado genérico
        $temasSeleccionados = [];
        if (!empty($postData['temas']) && is_array($postData['temas'])) {
            foreach ($postData['temas'] as $tema) {
                if (is_numeric($tema)) {
                    $temasSeleccionados[] = (int) $tema;
                }
            }
        }
        $clean['temas'] = $temasSeleccionados;

        // 3. Validar datos
        $errors = ValidationUtility::validateForm($clean);

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors'  => $errors,
                'message' => '',
                'old'     => $clean,
            ];
        }

        // 4. Verificar duplicados antes de insertar
        if ($this->userModel->existsUser($clean['identidad'], $clean['correo'])) {
            return [
                'success' => false,
                'errors'  => ['general' => 'Ya existe un registro con esa identidad o correo electrónico.'],
                'message' => '',
                'old'     => $clean,
            ];
        }

        // 5. Insertar en base de datos
        try {
            $idInscriptor = $this->userModel->insertUser($clean);

            // Regenerar token CSRF tras envío exitoso (previene reenvío del formulario)
            SecurityUtility::regenerateCsrfToken();

            return [
                'success' => true,
                'errors'  => [],
                'message' => "Inscripción registrada exitosamente (ID: {$idInscriptor}).",
                'old'     => [],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'errors'  => ['general' => $e->getMessage()],
                'message' => '',
                'old'     => $clean,
            ];
        }
    }
}