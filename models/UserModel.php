<?php
/**
 * UserModel.php
 * Modelo de acceso a datos para inscriptores, países y áreas de interés.
 * La base de datos parcial_itech y sus tablas ya existen; este modelo
 * solo consulta e inserta — nunca crea ni altera estructuras.
 *
 * Tablas que utiliza:
 *   - inscriptores        (datos principales del inscriptor)
 *   - paises              (catálogo para el <select> de país)
 *   - areas_interes       (catálogo para el <select multiple> de áreas)
 *   - inscriptor_temas    (relación N:M inscriptor ↔ área)
 */

require_once __DIR__ . '/Database.php';

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ----------------------------------------------------------------
    // Catálogos — datos para las listas desplegables
    // ----------------------------------------------------------------

    /**
     * Obtiene todos los países de la tabla `paises`, ordenados
     * alfabéticamente, para construir el <select> en el formulario.
     *
     * @return array<int, array{id_pais: int, nombre: string}>
     */
    public function getPaises(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre FROM paises ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las áreas de interés de la tabla `areas_interes`,
     * ordenadas alfabéticamente, para el <select multiple> del formulario.
     *
     * @return array<int, array{id_area: int, nombre: string}>
     */
    public function getAreasInteres(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nombre FROM areas_interes ORDER BY nombre ASC"
        );
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Inserción de inscriptores
    // ----------------------------------------------------------------

    /**
     * Inserta un nuevo inscriptor y sus áreas de interés seleccionadas.
     * Usa una transacción para mantener consistencia entre `inscriptores`
     * e `inscriptor_temas`.
     *
     * @param array $data Datos ya validados y sanitizados del formulario.
     *                    Estructura esperada:
     *                    [nombre, apellido, edad, sexo, id_pais,
     *                     nacionalidad, correo, celular, observaciones,
     *                     areas (array de enteros)]
     * @return int ID del inscriptor recién insertado.
     * @throws Exception Si ocurre un error en la inserción o por duplicado.
     */
    public function insertUser(array $data): int
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO inscriptores
                        (nombre, apellido, edad, sexo, id_pais, nacionalidad,
                         correo, celular, observaciones)
                    VALUES
                        (:nombre, :apellido, :edad, :sexo, :id_pais, :nacionalidad,
                         :correo, :celular, :observaciones)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre'        => $data['nombre'],
                ':apellido'      => $data['apellido'],
                ':edad'          => (int) $data['edad'],
                ':sexo'          => $data['sexo'],
                ':id_pais'       => (int) $data['id_pais'],
                ':nacionalidad'  => $data['nacionalidad'] ?? null,
                ':correo'        => $data['correo'],
                ':celular'       => $data['celular'],
                ':observaciones' => $data['observaciones'] ?? null,
            ]);

            $idInscriptor = (int) $this->db->lastInsertId();

            // Relacionar áreas de interés seleccionadas
            if (!empty($data['areas']) && is_array($data['areas'])) {
                $sqlArea = "INSERT INTO inscriptor_temas (id_inscriptor, id_area)
                            VALUES (:id_inscriptor, :id_area)";
                $stmtArea = $this->db->prepare($sqlArea);

                foreach ($data['areas'] as $idArea) {
                    $stmtArea->execute([
                        ':id_inscriptor' => $idInscriptor,
                        ':id_area'       => (int) $idArea,
                    ]);
                }
            }

            $this->db->commit();
            return $idInscriptor;

        } catch (PDOException $e) {
            $this->db->rollBack();

            // Código 23000 = Duplicate entry (correo/identidad repetida)
            if ((int) $e->getCode() === 23000) {
                throw new Exception('Ya existe un inscriptor registrado con ese correo electrónico.');
            }

            error_log('Error al insertar inscriptor: ' . $e->getMessage());
            throw new Exception('Error al guardar el registro. Inténtelo nuevamente.');
        }
    }

    // ----------------------------------------------------------------
    // Lectura de inscriptores
    // ----------------------------------------------------------------

    /**
     * Obtiene todos los inscriptores junto con su país y sus áreas
     * de interés concatenadas por comas (para reporte y vista).
     *
     * @return array
     */
    public function getUsers(): array
    {
        $sql = "SELECT
                    i.id_inscriptor,
                    i.nombre,
                    i.apellido,
                    i.edad,
                    i.sexo,
                    p.nombre  AS pais_residencia,
                    i.correo,
                    i.celular,
                    GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') AS areas,
                    i.fecha_registro
                FROM inscriptores i
                LEFT JOIN paises p         ON p.id  = i.id
                LEFT JOIN inscriptor_temas it ON it.id_inscriptor = i.id_inscriptor
                LEFT JOIN areas_interes a  ON a.id  = it.id
                GROUP BY
                    i.id_inscriptor, i.nombre, i.apellido, i.edad, i.sexo,
                    p.nombre, i.correo, i.celular, i.fecha_registro
                ORDER BY i.fecha_registro DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Verifica si ya existe un inscriptor con el correo indicado.
     *
     * @param string $correo Correo a verificar.
     * @return bool
     */
    public function existeCorreo(string $correo): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM inscriptores WHERE correo = :correo"
        );
        $stmt->execute([':correo' => $correo]);
        return (int) $stmt->fetchColumn() > 0;
    }
}