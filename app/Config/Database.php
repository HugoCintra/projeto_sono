<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use RuntimeException;

final class Database
{
    public static function connect(): PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $name = getenv('DB_NAME') ?: 'administrador_agenda_sono';
        $user = getenv('DB_USER') ?: 'administrador_root';
        $pass = getenv('DB_PASS') ?: 'Gcch2383#a';
        try {
            return new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (\PDOException $e) {
            throw new RuntimeException('Não foi possível conectar ao banco de dados.', 0, $e);
        }
    }
}
