<?php

require_once __DIR__ . '/Environments.php';

/**
 * Clase para manejar la conexión a la base de datos utilizando el patrón Singleton.
 *
 * Esta clase se encarga de establecer y proveer una única instancia de conexión a la base de datos
 * para cada solicitud, utilizando las configuraciones definidas en el archivo de entorno.
 *
 * @package config
 * @author Ruben
 * @version 1.0
 */
class Database
{
    /**
     * Conexión a la base de datos.
     *
     * @var mysqli
     */
    private $connection;
    
    /**
     * Instancia única de la clase Database.
     *
     * @var Database|null
     */
    private static $instance = null;

    /**
     * Constructor de la clase Database.
     *
     * Inicializa la conexión a la base de datos utilizando las configuraciones definidas
     * en el archivo de entorno. Se lanza una excepción si ocurre algún error en la conexión
     * o al configurar el charset.
     *
     * @throws Exception Si hay un error de conexión o al configurar el charset.
     */
    public function __construct()
    {
        $config = Environments::read();

        $host     = $config['DB_HOST'] ?? 'localhost';
        $user     = $config['DB_USER'] ?? 'root';
        $password = $config['DB_PASSWORD'] ?? '';
        $dbname   = $config['DB_NAME'] ?? '';

        $this->connection = new mysqli($host, $user, $password, $dbname);

        if ($this->connection->connect_error) {
            throw new Exception('Error de conexión: ' . $this->connection->connect_error);
        }
        if (!$this->connection->set_charset("utf8mb4")) {
            throw new Exception('Error configurando el charset: ' . $this->connection->error);
        }
    }

    /**
     * Obtiene la instancia única de la clase Database.
     *
     * Este método implementa el patrón Singleton, asegurando que solo exista una instancia
     * de Database por solicitud. Si aún no se ha creado, la instancia se inicializa.
     *
     * @return Database La instancia única de Database.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión activa a la base de datos.
     *
     * @return mysqli La conexión activa a la base de datos.
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}
?>
