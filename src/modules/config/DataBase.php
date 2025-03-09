<?php

require_once __DIR__ . '/Environments.php';

/**
 * Clase para manejar la conexión a la base de datos.
 *
 * @author Ruben Diaz
 * @version 1.0
 */
class Database
{
    /**
     * Conexión a la base de datos.
     * @var mysqli
     */
    private $connection;

    /**
     * Constructor de la clase Database.
     * Inicializa la conexión a la base de datos utilizando las configuraciones del entorno.
     *
     * @throws Exception Si hay un error de conexión con la base de datos.
     */
    public function __construct()
    {
        $config = Environments::read();

        $host = $config['DB_HOST'] ?? 'localhost';
        $user = $config['DB_USER'] ?? 'root';
        $password = $config['DB_PASSWORD'] ?? '';
        $dbname = $config['DB_NAME'] ?? '';

        $this->connection = new mysqli($host, $user, $password, $dbname);

        if ($this->connection->connect_error) {
            throw new Exception('Error de conexión: ' . $this->connection->connect_error);
        }
    }

    /**
     * Obtiene la conexión activa de la base de datos.
     *
     * @return mysqli Instancia de la conexión a la base de datos.
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
?>
