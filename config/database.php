<?php
declare(strict_types=1);
require_once __DIR__ . "/env.php";
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = env_value("DB_HOST");
        $port = env_value("DB_PORT", "3306");
        $name = env_value("DB_NAME");
        if (
            !preg_match('/^[a-zA-Z0-9_.:-]+$/', $host) ||
            !preg_match('/^[0-9]+$/D', $port) ||
            !preg_match('/^[a-zA-Z0-9_]+$/', $name)
        ) {
            throw new RuntimeException("Configuração de banco inválida.");
        }
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
            env_value("DB_USER"),
            env_value("DB_PASS"),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }
    return $pdo;
}
/** Todas as consultas da aplicação passam por prepare + execute. */
function sql(string $query, array $parameters = []): PDOStatement
{
    $statement = db()->prepare($query);
    $statement->execute($parameters);
    return $statement;
}
