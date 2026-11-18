<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;
final class UserRepository
{
    public function __construct(private PDO $db) {}
    public function byEmail(string $email): ?array { $s=$this->db->prepare('SELECT * FROM usuarios WHERE email = ?'); $s->execute([$email]); return $s->fetch() ?: null; }
    public function create(string $name,string $email,string $hash,string $timezone): int { $s=$this->db->prepare('INSERT INTO usuarios (nome,email,senha_hash,fuso_horario) VALUES (?,?,?,?)'); $s->execute([$name,$email,$hash,$timezone]); return (int)$this->db->lastInsertId(); }
}
