<?php

/*
 * conexao.php
 * Responsável por criar e retornar a conexão com o banco de dados.
 *
 * Uso nos controllers: $pdo = Connection::getConnection();
 */

class Connection
{
    // Armazena a conexão para não abrir uma nova a cada chamada (Singleton)
    private static $conn = null;

    // Impede criar objetos com "new Connection()"
    private function __construct() {}

    public static function getConnection()
    {
        if (self::$conn === null) {
            self::$conn = new PDO(
                "mysql:host=localhost;port=3306;dbname=crud-sistema;charset=utf8mb4",
                "root",  // usuário do banco
                "",      // senha (vazio no XAMPP padrão)
                [
                    //  Mostra erros do banco através de exceções.
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Retorna apenas arrays associativos ($produto['nome']).
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // Usa Prepared Statements reais do MySQL, aumentando a segurança.
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        // Retorna a conexão para ser usada em outras partes do sistema. 
        return self::$conn;
    }
}
