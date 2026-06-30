<?php
/**
 * ValidationUtility.php
 * Funciones de validación de datos de entrada del formulario (ORIGINAL, intacto)
 * + capa de auditoría/firma digital para el módulo de reportes (AJUSTADA al
 * esquema real de la tabla `inscriptores`: nombre, apellido, edad, sexo,
 * correo, celular — no existe campo de identificación en la BD).
 */

class ValidationUtility
{
    // =========================================================
    // ============   VALIDACIONES ORIGINALES (form)  ==========
    // =========================================================

    /**
     * Valida formato de identidad (cédula panameña genérica: dígitos, guiones, letras).
     * Ejemplo aceptado: 8-888-8888 / PE-8-NT-888
     * NOTA: la tabla `inscriptores` actual NO tiene columna de identificación,
     * por lo que este validador queda disponible para uso futuro en el
     * formulario, pero no se aplica en el reporte/auditoría.
     */
    public static function isValidIdentidad(string $identidad): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9\-]{5,20}$/', $identidad);
    }

    /**
     * Valida que el nombre/apellido solo contenga letras (incluye tildes y ñ) y espacios.
     */
    public static function isValidNombre(string $nombre): bool
    {
        return (bool) preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,100}$/u', $nombre);
    }

    /**
     * Valida edad dentro de un rango razonable.
     */
    public static function isValidEdad($edad): bool
    {
        if (!is_numeric($edad)) {
            return false;
        }
        $edad = (int) $edad;
        return $edad >= 1 && $edad <= 120;
    }

    /**
     * Valida sexo contra el ENUM real de la tabla inscriptores:
     * 'Masculino', 'Femenino', 'Otro'.
     */
    public static function isValidSexo(string $sexo): bool
    {
        return in_array($sexo, ['Masculino', 'Femenino', 'Otro'], true);
    }

    /**
     * Valida país/nacionalidad: solo letras y espacios.
     * (Útil si en el formulario se captura el nombre del país como texto
     * libre antes de mapearlo a pais_residencia_id / nacionalidad_id).
     */
    public static function isValidPais(string $pais): bool
    {
        return (bool) preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,100}$/u', $pais);
    }

    /**
     * Valida formato de correo electrónico.
     */
    public static function isValidCorreo(string $correo): bool
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida número celular (acepta +, espacios, guiones, 7-15 dígitos).
     */
    public static function isValidCelular(string $celular): bool
    {
        $soloDigitos = preg_replace('/[^0-9]/', '', $celular);
        return preg_match('/^[0-9+\-\s]{7,20}$/', $celular) && strlen($soloDigitos) >= 7;
    }

    /**
     * Valida que se haya seleccionado al menos un tema tecnológico.
     */
    public static function isValidTemas($temas): bool
    {
        return is_array($temas) && count($temas) > 0;
    }

    /**
     * Valida observaciones (campo opcional, longitud máxima).
     */
    public static function isValidObservaciones(string $observaciones): bool
    {
        return strlen($observaciones) <= 1000;
    }

    /**
     * Ejecuta todas las validaciones sobre el arreglo de datos del formulario.
     * Retorna un arreglo de errores (vacío si todo es válido).
     *
     * NOTA: si tu formulario actual no captura "identidad", elimina o ignora
     * ese bloque para que coincida con los campos reales que envíes por POST.
     */
    public static function validateForm(array $data): array
    {
        $errors = [];

        if (isset($data['identidad']) && (empty($data['identidad']) || !self::isValidIdentidad($data['identidad']))) {
            $errors['identidad'] = 'La identidad ingresada no es válida.';
        }

        if (empty($data['nombre']) || !self::isValidNombre($data['nombre'])) {
            $errors['nombre'] = 'El nombre solo debe contener letras.';
        }

        if (empty($data['apellido']) || !self::isValidNombre($data['apellido'])) {
            $errors['apellido'] = 'El apellido solo debe contener letras.';
        }

        if (!isset($data['edad']) || !self::isValidEdad($data['edad'])) {
            $errors['edad'] = 'La edad debe ser un número entre 1 y 120.';
        }

        if (empty($data['sexo']) || !self::isValidSexo($data['sexo'])) {
            $errors['sexo'] = 'Seleccione un sexo válido (Masculino, Femenino, Otro).';
        }

        if (empty($data['pais_residencia']) || !self::isValidPais($data['pais_residencia'])) {
            $errors['pais_residencia'] = 'El país de residencia no es válido.';
        }

        if (empty($data['nacionalidad']) || !self::isValidPais($data['nacionalidad'])) {
            $errors['nacionalidad'] = 'La nacionalidad no es válida.';
        }

        if (empty($data['correo']) || !self::isValidCorreo($data['correo'])) {
            $errors['correo'] = 'El correo electrónico no es válido.';
        }

        if (empty($data['celular']) || !self::isValidCelular($data['celular'])) {
            $errors['celular'] = 'El número de celular no es válido.';
        }

        if (!self::isValidTemas($data['temas'] ?? null)) {
            $errors['temas'] = 'Seleccione al menos un tema tecnológico.';
        }

        if (isset($data['observaciones']) && !self::isValidObservaciones($data['observaciones'])) {
            $errors['observaciones'] = 'Las observaciones no deben superar 1000 caracteres.';
        }

        return $errors;
    }

    // =========================================================
    // ====   AUDITORÍA PARA REPORTES (ajustada al esquema real) ====
    // =========================================================

    // Clave secreta para firmar registros (HMAC). En producción debe
    // moverse a variable de entorno, nunca quedar hardcodeada.
    private static string $secretKey = 'PARCIAL_ITECH_SECRET_KEY_2024';

    /**
     * Sanitiza una cadena para salida segura en HTML (evita XSS en la vista).
     */
    public static function sanitize(?string $value): string
    {
        return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida un registro proveniente del reporte (columnas reales de la BD:
     * nombre, apellido, edad, sexo, correo, celular). Reutiliza exactamente
     * las mismas reglas del formulario para mantener una sola fuente de verdad.
     */
    public static function validateRecord(array $record): bool
    {
        $nombre   = $record['nombre'] ?? '';
        $apellido = $record['apellido'] ?? '';
        $edad     = $record['edad'] ?? null;
        $correo   = $record['correo'] ?? '';
        $celular  = $record['celular'] ?? '';
        $sexo     = $record['sexo'] ?? '';

        return $nombre !== '' && self::isValidNombre($nombre)
            && $apellido !== '' && self::isValidNombre($apellido)
            && self::isValidEdad($edad)
            && $correo !== '' && self::isValidCorreo($correo)
            && $celular !== '' && self::isValidCelular($celular)
            && self::isValidSexo($sexo);
    }

    /**
     * Genera una firma HMAC-SHA256 (OpenSSL) sobre los datos concatenados
     * del registro, garantizando autenticidad e inmutabilidad.
     */
    public static function signRecord(array $record): string
    {
        $payload = implode('|', [
            $record['nombre'] ?? '',
            $record['apellido'] ?? '',
            $record['edad'] ?? '',
            $record['correo'] ?? '',
            $record['celular'] ?? '',
            $record['sexo'] ?? '',
        ]);

        return hash_hmac('sha256', $payload, self::$secretKey);
    }

    /**
     * Verifica que la firma de un registro coincida con sus datos actuales.
     * Si no coincide, el registro fue alterado (comprometido).
     */
    public static function verifySignature(array $record, string $signature): bool
    {
        $expected = self::signRecord($record);
        return hash_equals($expected, $signature);
    }

    /**
     * Auditoría completa para el reporte: valida formato + firma de autenticidad.
     * Retorna 'green' (íntegro) o 'red' (comprometido/inválido).
     */
    public static function auditStatus(array $record): string
    {
        $isValid     = self::validateRecord($record);
        $signature   = self::signRecord($record);
        $isAuthentic = self::verifySignature($record, $signature);

        return ($isValid && $isAuthentic) ? 'green' : 'red';
    }
}