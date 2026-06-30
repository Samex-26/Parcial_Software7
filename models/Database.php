<?php
/**
 * Database.php
 * Clase de conexión y operaciones genéricas con PDO.
 * Trabaja sobre la base de datos existente: parcial_itech
 */

class Database
{
    private static ?Database $instance = null;
    private ?PDO $conn = null;

    // ---- Configuración de conexión ----
    private string $host   = 'localhost';
    private string $dbname = 'parcial_itech';
    private string $user   = 'root';
    private string $pass   = '';
    private string $charset = 'utf8mb4';

    private function __construct()
    {
        $this->connect();
    }

    /**
     * Patrón Singleton para reutilizar la misma conexión en todo el proyecto.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establece la conexión PDO con manejo de excepciones.
     */
    public function connect(): void
    {
        if ($this->conn !== null) {
            return;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // No exponemos detalles sensibles al usuario final
            error_log('Error de conexión a BD: ' . $e->getMessage());
            die('No fue posible conectar a la base de datos.');
        }
    }

    /**
     * Ejecuta una consulta directa (sin parámetros). Útil para SELECT simples.
     */
    public function query(string $sql): PDOStatement|false
    {
        try {
            return $this->conn->query($sql);
        } catch (PDOException $e) {
            error_log('Error en query(): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepara y ejecuta una sentencia con parámetros (protección contra SQL Injection).
     */
    public function prepare(string $sql, array $params = []): PDOStatement|false
    {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Error en prepare(): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve el objeto PDO crudo si se necesita en casos puntuales.
     */
    public function getConnection(): ?PDO
    {
        return $this->conn;
    }

    /**
     * Cierra la conexión liberando el recurso.
     */
    public function close(): void
    {
        $this->conn = null;
        self::$instance = null;
    }

    /**
     * Verifica y aplica (si no existen) las llaves foráneas requeridas
     * entre inscriptores, paises, areas_interes e inscriptor_temas.
     * No recrea tablas, solo asegura integridad referencial.
     */
    public function ensureForeignKeys(): void
{
    $alters = [
        "ALTER TABLE inscriptores
            ADD CONSTRAINT fk_inscriptores_pais_residencia
            FOREIGN KEY (pais_residencia_id) REFERENCES paises(id)
            ON DELETE RESTRICT ON UPDATE CASCADE",

        "ALTER TABLE inscriptores
            ADD CONSTRAINT fk_inscriptores_nacionalidad
            FOREIGN KEY (nacionalidad_id) REFERENCES paises(id)
            ON DELETE RESTRICT ON UPDATE CASCADE",

        "ALTER TABLE inscriptor_temas
            ADD CONSTRAINT fk_temas_inscriptor
            FOREIGN KEY (inscriptor_id) REFERENCES inscriptores(id)
            ON DELETE RESTRICT ON UPDATE CASCADE",

        "ALTER TABLE inscriptor_temas
            ADD CONSTRAINT fk_temas_area
            FOREIGN KEY (area_interes_id) REFERENCES areas_interes(id)
            ON DELETE RESTRICT ON UPDATE CASCADE",
    ];

    foreach ($alters as $sql) {
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            if (!str_contains($e->getMessage(), 'Duplicate') &&
                !str_contains($e->getMessage(), 'already exists')) {
                error_log('FK warning: ' . $e->getMessage());
            }
        }
    }
}
}