<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__,2).'/app/Config/Database.php';
spl_autoload_register(static function(string $class):void{if(str_starts_with($class,'App\\')){$file=dirname(__DIR__,2).'/app/'.str_replace('\\','/',substr($class,4)).'.php';if(is_file($file))require $file;}});
$db=App\Config\Database::connect();
$columns=$db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='usuarios'")->fetchAll(PDO::FETCH_COLUMN);
if(!in_array('perfil',$columns,true))$db->exec("ALTER TABLE usuarios ADD COLUMN perfil ENUM('user','admin') NOT NULL DEFAULT 'user' AFTER senha_hash");
if(!in_array('ativo',$columns,true))$db->exec("ALTER TABLE usuarios ADD COLUMN ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER perfil");
$table=$db->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='recomendacoes'")->fetchColumn();
if(!(int)$table)$db->exec("CREATE TABLE recomendacoes (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,titulo VARCHAR(160) NOT NULL,conteudo TEXT NOT NULL,criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Schema administrativo verificado.\n";
