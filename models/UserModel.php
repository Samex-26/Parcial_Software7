<?php
/**
 * UserModel.php
 * Modelo encargado del acceso a datos de la tabla `inscriptores`
 * y su relación con `inscriptor_temas`.
 */

require_once __DIR__ . '/../models/Database.php';

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta un nuevo inscriptor junto con sus temas seleccionados.
     * Usa una transacción para garantizar consistencia entre ambas tablas.
     *
     * @param array $data  Datos ya validados y sanitizados del formulario.
     * @return int         ID del inscriptor insertado.
     * @throws Exception    Si ocurre un error en la inserción.
     */
    public function insertUser(array $data): int
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO inscriptores
                        (identidad, nombre, apellido, edad, sexo, pais_residencia,
                         nacionalidad, correo, celular, observaciones)
                    VALUES
                        (:identidad, :nombre, :apellido, :edad, :sexo, :pais_residencia,
                         :nacionalidad, :correo, :celular, :observaciones)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':identidad'        => $data['identidad'],
                ':nombre'           => $data['nombre'],
                ':apellido'         => $data['apellido'],
                ':edad'             => (int) $data['edad'],
                ':sexo'             => $data['sexo'],
                ':pais_residencia'  => $data['pais_residencia'],
                ':nacionalidad'     => $data['nacionalidad'],
                ':correo'           => $data['correo'],
                ':celular'          => $data['celular'],
                ':observaciones'    => $data['observaciones'] ?? null,
            ]);

            $idInscriptor = (int) $this->db->lastInsertId();

            // Relacionar temas seleccionados (tabla inscriptor_temas)
            if (!empty($data['temas']) && is_array($data['temas'])) {
                $sqlTema = "INSERT INTO inscriptor_temas (id_inscriptor, id_tema)
                            VALUES (:id_inscriptor, :id_tema)";
                $stmtTema = $this->db->prepare($sqlTema);

                foreach ($data['temas'] as $idTema) {
                    $stmtTema->execute([
                        ':id_inscriptor' => $idInscriptor,
                        ':id_tema'       => (int) $idTema,
                    ]);
                }
            }

            $this->db->commit();
            return $idInscriptor;

        } catch (PDOException $e) {
            $this->db->rollBack();

            // Error por duplicado (identidad o correo ya registrados)
            if ((int) $e->getCode() === 23000) {
                throw new Exception('Ya existe un registro con esa identidad o correo electrónico.');
            }

            error_log('Error al insertar inscriptor: ' . $e->getMessage());
            throw new Exception('Ocurrió un error al guardar el registro. Intente nuevamente.');
        }
    }

    /**
     * Obtiene todos los inscriptores registrados, junto con sus temas (opcional).
     *
     * @return array Lista de inscriptores
     */
    public function getUsers(): array
    {
        try {
            $sql = "SELECT id_inscriptor, identidad, nombre, apellido, edad, sexo,
                           pais_residencia, nacionalidad, correo, celular,
                           observaciones, fecha_registro
                    FROM inscriptores
                    ORDER BY fecha_registro DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Error al obtener inscriptores: ' . $e->getMessage());
            throw new Exception('No fue posible obtener la lista de registros.');
        }
    }

    /**
     * Obtiene todos los temas tecnológicos disponibles para mostrarlos en el formulario.
     */
    public function getTemas(): array
    {
        try {
            $stmt = $this->db->query("SELECT id_tema, nombre FROM temas ORDER BY nombre ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error al obtener temas: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si ya existe un registro con la identidad o correo dados.
     */
    public function existsUser(string $identidad, string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM inscriptores WHERE identidad = :identidad OR correo = :correo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':identidad' => $identidad, ':correo' => $correo]);
        return (int) $stmt->fetchColumn() > 0;
    }
}