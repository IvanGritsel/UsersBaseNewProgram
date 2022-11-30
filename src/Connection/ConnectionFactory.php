<?php

namespace App\Connection;

use App\Exception\ConnectionException;
use Exception;
use PDO;
use PDOException;

class ConnectionFactory
{
    private static ConnectionFactory $instance;

    private string $SERVER_NAME;
    private string $DB_NAME;
    private string $USER_NAME;
    private string $PASSWORD;

    private function __construct()
    {
        $fileContents = json_decode(file_get_contents(__DIR__ . '/../../resource/config/db_config.json'), true);
        $this->SERVER_NAME = $fileContents['servername'];
        $this->DB_NAME = $fileContents['dbname'];
        $this->USER_NAME = $fileContents['username'];
        $this->PASSWORD = $fileContents['password'];
    }

    public static function getInstance(): ConnectionFactory
    {
        if (!isset(self::$instance)) {
            self::$instance = new ConnectionFactory();
        }

        return self::$instance;
    }

    /**
     * @throws ConnectionException
     */
    public function getConnection(): PDO
    {
        try {
            $connection = new PDO(
                'mysql:host=' . $this->SERVER_NAME . ';dbname=' . $this->DB_NAME,
                $this->USER_NAME,
                $this->PASSWORD
            );
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $connection;
        } catch (PDOException $e) {
            $problem = $e->getMessage();

            throw new ConnectionException("Unable to get connection to database, cause: $problem", 503, $e);
        }
    }

    private function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Calling this method is not allowed');
    }
}
