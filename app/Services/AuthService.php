<?php
declare(strict_types=1);
namespace App\Services;
use App\Repositories\UserRepository;
final class AuthService
{
    public function __construct(private UserRepository $users) {}
    public function register(string $name,string $email,string $password,string $timezone): void {
        if(mb_strlen($name)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<10 || !in_array($timezone,\DateTimeZone::listIdentifiers(),true)) throw new \InvalidArgumentException('Informe nome, e-mail, senha (mínimo 10 caracteres) e fuso horário válidos.');
        if($this->users->byEmail(mb_strtolower($email))) throw new \InvalidArgumentException('Este e-mail já está cadastrado.');
        $this->users->create(trim($name),mb_strtolower($email),password_hash($password,PASSWORD_DEFAULT),$timezone);
    }
    public function login(string $email,string $password): bool { $user=$this->users->byEmail(mb_strtolower(trim($email))); if(!$user || !(bool)$user['ativo'] || !password_verify($password,$user['senha_hash'])) return false; session_regenerate_id(true); $_SESSION['user']=['id'=>(int)$user['id'],'nome'=>$user['nome'],'fuso_horario'=>$user['fuso_horario'],'perfil'=>$user['perfil']]; return true; }
}
