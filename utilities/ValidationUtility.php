<?php
/**
 * ValidationUtility.php
 *
 * VERSIÓN FUSIONADA:
 *   ✔ Tu clase original conservada intacta (todos los métodos, firma, lógica).
 *   ✚ Métodos nuevos AGREGADOS al final de cada sección:
 *       - isValidNombreTitulo()  → valida formato Título (Title Case).
 *       - isValidApellido()      → alias semántico de isValidNombre().
 *       - isValidAreas()         → valida selección de áreas de interés (FK).
 *       - validateForm() extendido con bloques opcionales para id_pais y areas[].
 *
 *   La sección de auditoría/firma digital (sanitize, validateRecord, signRecord,
 *   verifySignature, auditStatus) se conserva exactamente como la tenías.
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
     * (Tu validación original — conservada sin cambios.)
     */
    public static function isValidNombre(string $nombre): bool
    {
        return (bool) preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]{2,100}$/u', $nombre);
    }

    /**
     * ✚ NUEVO — Alias semántico de isValidNombre() para mayor claridad en el código
     * del formulario cuando se valida específicamente el campo "apellido".
     * Aplica exactamente las mismas reglas; no rompe ningún código existente.
     */
    public static function isValidApellido(string $apellido): bool
    {
        return self::isValidNombre($apellido);
    }

    /**
     * ✚ NUEVO — Valida formato Título (Title Case): cada palabra debe iniciar
     * con mayúscula, admite letras con tilde, ñ y guiones entre palabras.
     *
     * Ejemplo válido  : "María José", "De La Cruz"
     * Ejemplo inválido: "maría josé" / "MARÍA JOSÉ"
     *
     * Úsalo cuando el formulario requiera este formato estricto.
     * Tu isValidNombre() original sigue disponible para validación básica.
     */
    public static function isValidNombreTitulo(string $nombre): bool
    {
        $nombre = trim($nombre);
        if ($nombre === '' || mb_strlen($nombre, 'UTF-8') < 2 || mb_strlen($nombre, 'UTF-8') > 100) {
            return false;
        }
        // Divide por espacio o guion y evalúa cada palabra
        $palabras = preg_split('/[\s\-]+/u', $nombre);
        foreach ($palabras as $palabra) {
            if ($palabra === '') {
                continue;
            }
            $primeraLetra = mb_substr($palabra, 0, 1, 'UTF-8');
            if (mb_strtoupper($primeraLetra, 'UTF-8') !== $primeraLetra) {
                return false;
            }
            if (!preg_match('/^[\p{L}\'-]+$/u', $palabra)) {
                return false;
            }
        }
        return true;
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
     * ✚ NUEVO — Valida que se haya seleccionado al menos un área de interés
     * (FK a la tabla `areas_interes`) y que todos los valores sean enteros positivos.
     * Complementa isValidTemas() para el formulario con <select multiple>.
     */
    public static function isValidAreas($areas): bool
    {
        if (!is_array($areas) || count($areas) === 0) {
            return false;
        }
        foreach ($areas as $id) {
            if (!is_numeric($id) || (int) $id <= 0) {
                return false;
            }
        }
        return true;
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
     * ✚ EXTENSIÓN: se agregaron bloques opcionales al final para validar
     *   `id_pais`  (ID entero, FK a tabla `paises`) y
     *   `areas[]`  (arreglo de IDs, FK a tabla `areas_interes`).
     *   Son opcionales: solo se validan si las claves están presentes en $data,
     *   de modo que tu formulario actual no se ve afectado si no los envía.
     *
     * NOTA: si tu formulario actual no captura "identidad", elimina o ignora
     * ese bloque para que coincida con los campos reales que envíes por POST.
     */
    public static function validateForm(array $data): array
    {
        $errors = [];

        // ---- Campos originales (tu lógica sin cambios) ----

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

        // ---- ✚ Bloques nuevos (opcionales, no afectan formularios existentes) ----

        // Valida ID de país cuando el formulario usa <select> dinámico (tabla paises).
        // Solo se activa si la clave 'id_pais' está presente en $data.
        if (array_key_exists('id_pais', $data)) {
            if (empty($data['id_pais']) || !is_numeric($data['id_pais']) || (int) $data['id_pais'] <= 0) {
                $errors['id_pais'] = 'Seleccione un país de residencia válido.';
            }
        }

        // Valida áreas de interés cuando el formulario usa <select multiple> (tabla areas_interes).
        // Solo se activa si la clave 'areas' está presente en $data.
        if (array_key_exists('areas', $data)) {
            if (!self::isValidAreas($data['areas'])) {
                $errors['areas'] = 'Seleccione al menos un área de interés.';
            }
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