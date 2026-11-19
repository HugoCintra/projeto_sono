<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;
final class AdminRepository
{
    public function __construct(private PDO $db) {}
    public function statistics(): array { return $this->db->query('SELECT (SELECT COUNT(*) FROM usuarios) usuarios,(SELECT COUNT(*) FROM registros_sono) registros,(SELECT AVG(TIMESTAMPDIFF(SECOND,inicio_em,fim_em))/3600 FROM registros_sono) media_horas')->fetch() ?: []; }
    public function recommendations(): array { return $this->db->query('SELECT id,titulo,conteudo,criado_em,atualizado_em FROM recomendacoes ORDER BY atualizado_em DESC')->fetchAll(); }
    public function saveRecommendation(?int $id,string $title,string $body): void { if($id){$q=$this->db->prepare('UPDATE recomendacoes SET titulo=?,conteudo=? WHERE id=?');$q->execute([$title,$body,$id]);}else{$q=$this->db->prepare('INSERT INTO recomendacoes (titulo,conteudo) VALUES (?,?)');$q->execute([$title,$body]);} }
    public function deleteRecommendation(int $id): void { $q=$this->db->prepare('DELETE FROM recomendacoes WHERE id=?');$q->execute([$id]); }
}
