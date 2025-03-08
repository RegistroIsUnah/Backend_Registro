<?php

__DIR__ . '/../modules/config/Environments.php';

class Database
{
    private $connection;

    public function __construct()
    {
        $config = Environments::read();

        $host = $config['DB_HOST'] ?? 'localhost';
        $user = $config['DB_USER'] ?? 'root';
        $password = $config['DB_PASSWORD'] ?? '';
        $dbname = $config['DB_NAME'] ?? '';


        $this->connection = new mysqli($host, $user, $password, $dbname);

        if ($this->connection->connect_error) {
            die('Error de conexión: ' . $this->connection->connect_error);
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
