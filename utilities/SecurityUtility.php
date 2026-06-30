<?php
/**
 * SecurityUtility.php
 * Funciones de seguridad: sanitización, escape de salidas y CSRF tokens.
 */

class SecurityUtility
{
    /**
     * Sanitiza una cadena de entrada eliminando espacios y etiquetas HTML peligrosas.
     */
    public static function sanitizeInput(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        $value = trim($value);
        $value = stripslashes($value);
        // Elimina etiquetas HTML/PHP
        $value = strip_tags($value);
        return $value;
    }

    /**
     * Sanitiza un arreglo completo de entradas (ej. $_POST)
     */
    public static function sanitizeArray(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = array_map([self::class, 'sanitizeInput'], $value);
            } else {
                $clean[$key] = self::sanitizeInput($value);
            }
        }
        return $clean;
    }

    /**
     * Escapa una cadena para mostrarla de forma segura en HTML (previene XSS).
     */
    public static function escapeOutput(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Genera (o reutiliza) un token CSRF y lo guarda en sesión.
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
     * Valida el token CSRF recibido contra el almacenado en sesión.
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenera el token CSRF (recomendado tras un envío exitoso).
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