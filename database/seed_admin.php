<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/Config/Database.php';
spl_autoload_register(static function(string $class):void{if(str_starts_with($class,'App\\')){$file=dirname(__DIR__).'/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($file))require $file;}});
$db=App\Config\Database::connect();
$q=$db->prepare('SELECT perfil FROM usuarios WHERE email=?');$q->execute(['admin@sonosaudavel.com']);$existing=$q->fetchColumn();
if($existing!==false){if($existing!=='admin'){fwrite(STDERR,"O e-mail já pertence a uma conta comum; nenhuma alteração foi feita.\n");exit(1);}echo "Conta administrativa já existe.\n";exit;}
$q=$db->prepare('INSERT INTO usuarios (nome,email,senha_hash,fuso_horario,perfil,ativo) VALUES (?,?,?,?,?,1)');$q->execute(['Administrador','admin@sonosaudavel.com',password_hash('Admin@Sono2026',PASSWORD_DEFAULT),'America/Sao_Paulo','admin']);
echo "Conta administrativa inicial criada. Troque a senha após o primeiro acesso.\n";
