<?php

namespace App\Mail\Database;

use PDO;
use PDOException;

class Connection
{
    public static PDO $connection;

    private function __construct()
    {
    }

    private static function connect(): PDO
    {
        try {
            $connection = new PDO(
                "pgsql:host={$_ENV['CONF_DB_HOST']};
                dbname={$_ENV['CONF_DB_NAME']};
                port={$_ENV['CONF_DB_PORT']}",
                $_ENV['CONF_DB_USER'],
                $_ENV['CONF_DB_PASS']
            );

            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);

            return $connection;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            die;
        }
    }

    public static function getConnection(): ?PDO
    {
        if (empty(self::$connection)) {
            self::$connection = self::connect();
            if (!self::$connection instanceof PDO) {
                return null;
            }
        }
        return self::$connection;
    }
}
