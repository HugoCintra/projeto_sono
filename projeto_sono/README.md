# Agenda de Sono

Aplicação PHP 8 + MySQL, com páginas renderizadas no servidor.

## Configuração local (XAMPP)

1. Crie um banco MySQL chamado `agenda_sono` usando `utf8mb4`.
2. Importe `database/migrations/001_initial.sql` pelo phpMyAdmin ou cliente MySQL.
3. Configure `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` no ambiente do Apache. Os padrões são `127.0.0.1`, `agenda_sono`, `root` e senha vazia (padrão comum de XAMPP local).
4. Configure o VirtualHost para usar `public/` como DocumentRoot e habilite `mod_rewrite` e `.htaccess`. Alternativamente, acesse `public/index.php?r=login` diretamente.

O MVP inclui autenticação, agenda semanal, padrões por dia, exceções, registro manual de sono, dashboard e histórico de 30 dias. O sono atravessando a meia-noite deve ser enviado como datetimes de dias diferentes. Os registros são privados por conta.
