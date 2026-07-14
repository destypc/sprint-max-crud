<?php

class Connection
{
    private static $conexao = null;

    private function __construct() {}

    public static function getConnection()
    {
        if (self::$conexao === null) {
            self::$conexao = new PDO(
                "mysql:host=localhost;port=3306;dbname=crud-sistema;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        return self::$conexao;
    }
}
