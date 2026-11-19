<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;
final class UserRepository
{
    public function __construct(private PDO $db) {}
    public function byEmail(string $email): ?array { $s=$this->db->prepare('SELECT * FROM usuarios WHERE email = ?'); $s->execute([$email]); return $s->fetch() ?: null; }
    public function create(string $name,string $email,string $hash,string $timezone): int { $s=$this->db->prepare('INSERT INTO usuarios (nome,email,senha_hash,fuso_horario) VALUES (?,?,?,?)'); $s->execute([$name,$email,$hash,$timezone]); return (int)$this->db->lastInsertId(); }
    public function accessById(int $id): ?array { $s=$this->db->prepare('SELECT perfil,ativo FROM usuarios WHERE id=?');$s->execute([$id]);return $s->fetch()?:null; }
    public function search(string $term,int $limit,int $offset): array { $s=$this->db->prepare('SELECT id,nome,email,perfil,ativo,criado_em FROM usuarios WHERE nome LIKE ? OR email LIKE ? ORDER BY criado_em DESC LIMIT ? OFFSET ?');$like='%'.$term.'%';$s->bindValue(1,$like);$s->bindValue(2,$like);$s->bindValue(3,$limit,PDO::PARAM_INT);$s->bindValue(4,$offset,PDO::PARAM_INT);$s->execute();return $s->fetchAll(); }
    public function countSearch(string $term): int { $s=$this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE nome LIKE ? OR email LIKE ?');$like='%'.$term.'%';$s->execute([$like,$like]);return (int)$s->fetchColumn(); }
    public function deleteCommon(int $id): void { $s=$this->db->prepare("DELETE FROM usuarios WHERE id=? AND perfil='user'");$s->execute([$id]); }
}
