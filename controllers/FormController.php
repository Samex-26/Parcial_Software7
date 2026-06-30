<?php
/**
 * FormController.php
 * Controlador del formulario de inscripción.
 * Flujo de trabajo por solicitud POST:
 *   1. Validar token CSRF.
 *   2. Sanitizar entradas con SecurityUtility.
 *   3. Validar datos con ValidationUtility.
 *   4. Verificar duplicado de correo.
 *   5. Insertar inscriptor con áreas relacionadas.
 *   6. Redirigir a success.php o devolver errores.
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
     * Procesa la solicitud POST del formulario.
     *
     * @param array $postData Datos crudos de $_POST.
     * @return array{success: bool, errors: array, message: string, old: array}
     */
    public function handleSubmit(array $postData): array
    {
        // 1. Protección CSRF
        if (!SecurityUtility::validateCsrfToken($postData['csrf_token'] ?? null)) {
            return [
                'success' => false,
                'errors'  => ['csrf' => 'Token de seguridad inválido. Recargue la página e intente de nuevo.'],
                'message' => '',
                'old'     => [],
            ];
        }

        // 2. Sanitización (el arreglo de áreas se trata como IDs enteros)
        $clean = SecurityUtility::sanitizeArray($postData);

        // 3. Validación
        $errors = ValidationUtility::validateForm($clean);

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors'  => $errors,
                'message' => '',
                'old'     => $clean,
            ];
        }

        // 4. Verificar correo duplicado
        if ($this->userModel->existeCorreo($clean['correo'])) {
            return [
                'success' => false,
                'errors'  => ['correo' => 'Ya existe un inscriptor registrado con ese correo electrónico.'],
                'message' => '',
                'old'     => $clean,
            ];
        }

        // 5. Insertar
        try {
            $idInscriptor = $this->userModel->insertUser($clean);
            SecurityUtility::regenerateCsrfToken();

            return [
                'success'       => true,
                'errors'        => [],
                'message'       => 'Inscripción registrada exitosamente.',
                'id_inscriptor' => $idInscriptor,
                'old'           => [],
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