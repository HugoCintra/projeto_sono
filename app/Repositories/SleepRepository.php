<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;
final class SleepRepository
{
    public function __construct(private PDO $db) {}
    public function plans(int $uid): array { $s=$this->db->prepare('SELECT p.*, GROUP_CONCAT(d.dia_semana ORDER BY d.dia_semana) dias FROM planejamentos_sono p LEFT JOIN planejamentos_dias d ON d.planejamento_id=p.id WHERE p.usuario_id=? AND p.ativo=1 GROUP BY p.id ORDER BY p.valido_desde DESC'); $s->execute([$uid]); return $s->fetchAll(); }
    public function addPlan(int $uid,string $bed,string $wake,array $days,string $from,?string $to=null): void {
        $this->db->beginTransaction(); try { $q=$this->db->prepare('INSERT INTO planejamentos_sono (usuario_id,hora_dormir,hora_acordar,valido_desde,valido_ate) VALUES (?,?,?,?,?)'); $q->execute([$uid,$bed,$wake,$from,$to]); $id=(int)$this->db->lastInsertId(); $d=$this->db->prepare('INSERT INTO planejamentos_dias (planejamento_id,dia_semana) VALUES (?,?)'); foreach(array_unique($days) as $day)$d->execute([$id,$day]); $this->db->commit(); } catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }
    public function deletePlan(int $uid,int $id): void { $s=$this->db->prepare('DELETE FROM planejamentos_sono WHERE id=? AND usuario_id=?'); $s->execute([$id,$uid]); }
    public function exception(int $uid,string $date): ?array { $s=$this->db->prepare('SELECT * FROM excecoes_agenda WHERE usuario_id=? AND data_inicio=?'); $s->execute([$uid,$date]); return $s->fetch() ?: null; }
    public function saveException(int $uid,string $date,bool $cancel,?string $bed,?string $wake): void { $s=$this->db->prepare('INSERT INTO excecoes_agenda (usuario_id,data_inicio,cancelada,hora_dormir,hora_acordar) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE cancelada=VALUES(cancelada),hora_dormir=VALUES(hora_dormir),hora_acordar=VALUES(hora_acordar)'); $s->execute([$uid,$date,(int)$cancel,$bed,$wake]); }
    public function plansForDate(int $uid,string $date,int $weekday): ?array {
        $ex=$this->exception($uid,$date); if($ex){return (bool)$ex['cancelada']?null:['hora_dormir'=>$ex['hora_dormir'],'hora_acordar'=>$ex['hora_acordar'],'excecao'=>true];}
        $s=$this->db->prepare('SELECT p.hora_dormir,p.hora_acordar FROM planejamentos_sono p JOIN planejamentos_dias d ON d.planejamento_id=p.id WHERE p.usuario_id=? AND p.ativo=1 AND p.valido_desde<=? AND (p.valido_ate IS NULL OR p.valido_ate>=?) AND d.dia_semana=? ORDER BY p.valido_desde DESC LIMIT 1'); $s->execute([$uid,$date,$date,$weekday]); return $s->fetch() ?: null;
    }
    public function addRecord(int $uid,string $start,string $end,?string $note,?int $quality): void { $s=$this->db->prepare('INSERT INTO registros_sono (usuario_id,inicio_em,fim_em,observacao,qualidade) VALUES (?,?,?,?,?)'); $s->execute([$uid,$start,$end,$note,$quality]); }
    public function records(int $uid,string $from,string $to): array { $s=$this->db->prepare('SELECT * FROM registros_sono WHERE usuario_id=? AND inicio_em>=? AND inicio_em<? ORDER BY inicio_em DESC'); $s->execute([$uid,$from,$to]); return $s->fetchAll(); }
}
