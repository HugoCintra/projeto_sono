<?php
declare(strict_types=1);

use App\Config\Database;
use App\Repositories\SleepRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;

require dirname(__DIR__) . '/app/Support/helpers.php';
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) require $file;
    }
});
session_name('administrador_agenda_sono');
session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']), 'samesite' => 'Lax', 'path' => '/']);
session_start();
$route = trim((string)($_GET['r'] ?? 'dashboard'), '/');
if (is_ajax_request() && in_array($route, ['plan-save', 'plan-delete', 'exception-save', 'record-save'], true)) {
    set_exception_handler(static function (Throwable $error): never {
        error_log((string)$error);
        json_response(false, 'Não foi possível concluir a operação.', [], ['server' => 'unexpected'], 500);
    });
}
try {
    $db = Database::connect();
    $users = new UserRepository($db);
    $auth = new AuthService($users);
    $sleep = new SleepRepository($db);
} catch (Throwable $e) {
    if (is_ajax_request()) json_response(false, 'Serviço temporariamente indisponível.', [], ['server' => 'unavailable'], 500);
    http_response_code(500);
    $error = 'Banco de dados indisponível. Crie o banco administrador_agenda_sono e aplique database/migrations/001_initial.sql.';
    require dirname(__DIR__) . '/app/Views/error.php';
    exit;
}
$user = $_SESSION['user'] ?? null;
if (in_array($route, ['logout', 'plan-save', 'plan-delete', 'exception-save', 'record-save'], true) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (is_ajax_request()) json_response(false, 'Método não permitido.', [], [], 405);
    http_response_code(405);
    exit('Método não permitido');
}
if ($route === 'logout') {
    verify_csrf();
    $_SESSION = [];
    session_destroy();
    redirect('login');
}
if ($route === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $auth->register(trim((string)($_POST['name'] ?? '')), trim((string)($_POST['email'] ?? '')), (string)($_POST['password'] ?? ''), (string)($_POST['timezone'] ?? 'America/Sao_Paulo'));
        flash('success', 'Conta criada. Entre com seus dados.');
        redirect('login');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($auth->login((string)($_POST['email'] ?? ''), (string)($_POST['password'] ?? ''))) redirect('dashboard');
    $error = 'E-mail ou senha inválidos.';
}
if (!$user && !in_array($route, ['login', 'register'], true)) redirect('login');
if ($user && in_array($route, ['login', 'register'], true)) redirect('dashboard');
if ($user && $route === 'plan-save') {
    verify_csrf();
    $days = array_map('intval', (array)($_POST['days'] ?? []));
    $bed = (string)($_POST['bed'] ?? '');
    $wake = (string)($_POST['wake'] ?? '');
    if (!$days || array_diff($days, range(1, 7)) || !preg_match('/^\d{2}:\d{2}$/', $bed) || !preg_match('/^\d{2}:\d{2}$/', $wake)) {
        if (is_ajax_request()) json_response(false, 'Confira horários e dias selecionados.', [], ['form' => 'invalid'], 422);
        flash('error', 'Confira horários e dias selecionados.');
        redirect('agenda');
    }
    $sleep->addPlan((int)$user['id'], $bed . ':00', $wake . ':00', $days, date('Y-m-d'));
    if (is_ajax_request()) json_response(true, 'Planejamento adicionado.');
    flash('success', 'Planejamento adicionado.');
    redirect('agenda');
}
if ($user && $route === 'plan-delete') {
    verify_csrf();
    $sleep->deletePlan((int)$user['id'], (int)($_POST['id'] ?? 0));
    if (is_ajax_request()) json_response(true, 'Planejamento removido.');
    flash('success', 'Planejamento removido.');
    redirect('agenda');
}
if ($user && $route === 'exception-save') {
    verify_csrf();
    $date = (string)($_POST['date'] ?? '');
    $cancel = isset($_POST['cancel']);
    $bed = (string)($_POST['bed'] ?? '');
    $wake = (string)($_POST['wake'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || (!$cancel && (!preg_match('/^\d{2}:\d{2}$/', $bed) || !preg_match('/^\d{2}:\d{2}$/', $wake)))) {
        if (is_ajax_request()) json_response(false, 'Confira a data e os horários da exceção.', [], ['form' => 'invalid'], 422);
        flash('error', 'Confira a data e os horários da exceção.');
        redirect('agenda');
    }
    $sleep->saveException((int)$user['id'], $date, $cancel, $cancel ? null : $bed . ':00', $cancel ? null : $wake . ':00');
    if (is_ajax_request()) json_response(true, 'Exceção salva.');
    flash('success', 'Exceção salva.');
    redirect('agenda');
}
if ($user && $route === 'record-save') {
    verify_csrf();
    try {
        $start = new DateTimeImmutable((string)($_POST['start'] ?? ''));
        $end = new DateTimeImmutable((string)($_POST['end'] ?? ''), new DateTimeZone($user['fuso_horario']));
        $seconds = $end->getTimestamp() - $start->getTimestamp();
        if ($seconds <= 0 || $seconds > 24 * 3600) throw new InvalidArgumentException('O período deve ser maior que zero e não ultrapassar 24 horas.');
        $quality = ($_POST['quality'] ?? '') === '' ? null : (int)$_POST['quality'];
        if ($quality !== null && ($quality < 1 || $quality > 5)) throw new InvalidArgumentException('Qualidade deve estar entre 1 e 5.');
        $sleep->addRecord((int)$user['id'], $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'), trim((string)($_POST['note'] ?? '')) ?: null, $quality);
        if (is_ajax_request()) json_response(true, 'Registro salvo.');
        flash('success', 'Registro salvo.');
    } catch (Throwable $e) {
        if (is_ajax_request()) json_response(false, $e instanceof InvalidArgumentException ? $e->getMessage() : 'Confira as datas e horários informados.', [], ['form' => 'invalid'], 422);
        flash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'Confira as datas e horários informados.');
    }
    redirect('record');
}
$today = new DateTimeImmutable('today', new DateTimeZone($user['fuso_horario'] ?? 'America/Sao_Paulo'));
$weekStart = $today->modify('monday this week');
$offset = (int)($_GET['offset'] ?? 0);
$weekStart = $weekStart->modify(($offset >= 0 ? '+' : '') . $offset . ' weeks');
$days = [];
if ($user) for ($i = 0; $i < 7; $i++) {
    $date = $weekStart->modify("+{$i} days");
    $days[] = ['date' => $date, 'plan' => $sleep->plansForDate((int)$user['id'], $date->format('Y-m-d'), (int)$date->format('N'))];
}
$plans = $user ? $sleep->plans((int)$user['id']) : [];
$records = $user ? $sleep->records((int)$user['id'], $weekStart->format('Y-m-d 00:00:00'), $weekStart->modify('+7 days')->format('Y-m-d 00:00:00')) : [];
$history = $user ? $sleep->records((int)$user['id'], $today->modify('-30 days')->format('Y-m-d 00:00:00'), $today->modify('+1 day')->format('Y-m-d 00:00:00')) : [];
$view = match ($route) {
    'login' => 'auth',
    'register' => 'auth',
    'agenda' => 'agenda',
    'record' => 'record',
    'history' => 'history',
    default => 'dashboard'
};
require dirname(__DIR__) . '/app/Views/' . $view . '.php';
