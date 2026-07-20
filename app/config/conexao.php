<?php

class Connection
{
    private static $conexao = null;

    private function __construct() {}

    public static function getConnection()
    {
        if (self::$conexao === null) {
            // Lê as credenciais de variáveis de ambiente (produção: Railway/host).
            // Se não houver, usa os padrões locais de desenvolvimento.
            $host  = getenv('MYSQLHOST')     ?: getenv('DB_HOST')     ?: 'localhost';
            $port  = getenv('MYSQLPORT')     ?: getenv('DB_PORT')     ?: '3306';
            $banco = getenv('MYSQLDATABASE') ?: getenv('DB_NAME')     ?: 'crud-sistema';
            $user  = getenv('MYSQLUSER')     ?: getenv('DB_USER')     ?: 'root';
            $senha = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';

            self::$conexao = new PDO(
                "mysql:host={$host};port={$port};dbname={$banco};charset=utf8mb4",
                $user,
                $senha,
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
