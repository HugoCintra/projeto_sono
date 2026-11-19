<?php
declare(strict_types=1);
namespace App\Services;
use App\Repositories\AdminRepository;
use App\Repositories\UserRepository;
final class AdminService
{
    public function __construct(private AdminRepository $admin,private UserRepository $users) {}
    public function listing(string $query,int $page): array { $query=trim($query);if(mb_strlen($query)>100)throw new \InvalidArgumentException('Pesquisa muito longa.');$page=max(1,$page);return ['items'=>$this->users->search($query,20,($page-1)*20),'total'=>$this->users->countSearch($query),'page'=>$page]; }
    public function saveRecommendation(?int $id,string $title,string $body): void { $title=trim($title);$body=trim($body);if($title===''||mb_strlen($title)>160||$body===''||mb_strlen($body)>5000)throw new \InvalidArgumentException('Informe título e conteúdo válidos.');$this->admin->saveRecommendation($id,$title,$body); }
    public function deleteUser(int $id,int $actor): void { if($id<1||$id===$actor)throw new \InvalidArgumentException('Não é possível excluir a própria conta.');$this->users->deleteCommon($id); }
}
