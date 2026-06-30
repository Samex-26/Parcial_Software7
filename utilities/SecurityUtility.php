<?php
/**
 * SecurityUtility.php
 * Clase estática de seguridad:
 *   - Sanitización de entradas individuales y en lote.
 *   - Escape de salidas HTML (prevención de XSS).
 *   - Gestión de tokens CSRF en sesión.
 *
 * La sanitización NO reemplaza la validación: primero se sanitiza
 * para limpiar ruido accidental (espacios, barras escapadas, tags HTML),
 * y luego se valida con ValidationUtility para garantizar la forma.
 */class SecurityUtility
{
    // ----------------------------------------------------------------
    // Sanitización de entradas
    // ----------------------------------------------------------------

    /**
     * Sanitiza una cadena de texto de entrada:
     *   1. Elimina espacios al inicio y al final (trim).
     *   2. Elimina barras escapadas agregadas por magic_quotes (stripslashes).
     *   3. Elimina etiquetas HTML y PHP (strip_tags).
     *
     * @param string|null $valor Valor recibido del formulario.
     * @return string Cadena limpia y segura.
     */
    public static function sanitizeInput(?string $valor): string
    {
        if ($valor === null) {
            return '';
        }
        $valor = trim($valor);
        $valor = stripslashes($valor);
        $valor = strip_tags($valor);
        return $valor;
    }

    /**
     * Sanitiza un arreglo completo de datos (p.ej. $_POST), aplicando
     * sanitizeInput() a cada elemento escalar y recursión en sub-arreglos.
     *
     * Los elementos de tipo arreglo de enteros (como IDs de áreas/países)
     * se convierten a enteros directamente para evitar inyecciones.
     *
     * @param array $datos Arreglo de datos crudos del formulario.
     * @return array Arreglo sanitizado.
     */
    public static function sanitizeArray(array $datos): array
    {
        $clean = [];

        foreach ($datos as $clave => $valor) {
            if (is_array($valor)) {
                // Arreglos de IDs (áreas, temas): convertir cada elemento a entero
                $esArregloDeIds = array_reduce(
                    $valor,
                    fn(bool $carry, $item) => $carry && is_numeric($item),
                    true
                );

                if ($esArregloDeIds) {
                    $clean[$clave] = array_map('intval', $valor);
                } else {
                    $clean[$clave] = array_map([self::class, 'sanitizeInput'], $valor);
                }
            } else {
                $clean[$clave] = self::sanitizeInput((string) $valor);
            }
        }

        return $clean;
    }

    // ----------------------------------------------------------------
    // Escape de salidas HTML (prevención de XSS)
    // ----------------------------------------------------------------

    /**
     * Escapa una cadena para que sea segura mostrarla en HTML.
     * Convierte caracteres especiales (&, <, >, ", ') a entidades HTML.
     *
     * @param string|null $valor Valor que se va a imprimir en la vista.
     * @return string Cadena escapada.
     */
    public static function escapeOutput(?string $valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ----------------------------------------------------------------
    // Tokens CSRF
    // ----------------------------------------------------------------

    /**
     * Genera (o reutiliza) un token CSRF y lo almacena en sesión.
     * Debe llamarse antes de renderizar cualquier formulario.
     *
     * @return string Token CSRF listo para insertar en el campo oculto.
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF recibido en el POST contra el guardado en sesión.
     * Usa hash_equals() para prevenir ataques de comparación por tiempo.
     *
     * @param string|null $tokenRecibido Token enviado por el formulario.
     * @return bool true si el token es válido, false en caso contrario.
     */
    public static function validateCsrfToken(?string $tokenRecibido): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token']) || empty($tokenRecibido)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $tokenRecibido);
    }

    /**
     * Regenera el token CSRF (llamar tras un envío exitoso para
     * invalidar el token anterior y prevenir reenvíos).
     *
     * @return string Nuevo token CSRF.
     */
    public static function regenerateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}