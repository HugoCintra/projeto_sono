<?php $title = 'Agenda semanal';
require __DIR__ . '/layout.php'; ?><h1>Agenda semanal</h1>
<div class="toolbar"><a class="button" href="<?= e(url('agenda?offset=' . ($offset - 1))) ?>">← Semana
        anterior</a><strong><?= e($weekStart->format('d/m')) ?>
        – <?= e($weekStart->modify('+6 days')->format('d/m/Y')) ?></strong><a class="button"
                                                                              href="<?= e(url('agenda?offset=' . ($offset + 1))) ?>">Próxima
        semana →</a></div>
<div class="week"><?php foreach ($days as $day): ?>
        <article class="card day"><h2><?= e(['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'][(int)$day['date']->format('N') - 1] . ' ' . $day['date']->format('d/m')) ?></h2><?php if ($day['plan']): ?><p
                class="planned">Planejado: <?= e(substr($day['plan']['hora_dormir'], 0, 5)) ?>
            → <?= e(substr($day['plan']['hora_acordar'], 0, 5)) ?><?php if (!empty($day['plan']['excecao'])): ?> <span>(exceção)</span><?php endif; ?>
            </p><?php else: ?><p class="muted">Sem planejamento</p><?php endif; ?><p><a href="<?= e(url('record')) ?>">Registrar</a>
            · <a href="<?= e(url('agenda?date=' . $day['date']->format('Y-m-d'))) ?>#exception">Exceção</a></p>
        </article><?php endforeach; ?></div>
<div class="grid">
    <section class="card"><h2>Novo padrão semanal</h2>
        <form method="post" action="<?= e(url('plan-save')) ?>" data-ajax-form data-agenda-form><input type="hidden" name="_csrf"
                                                                       value="<?= e(csrf_token()) ?>"><label>Hora de
                dormir<input type="time" name="bed" required></label><label>Hora de acordar<input type="time"
                                                                                                  name="wake" required></label>
            <fieldset>
                <legend>Aplicar nos dias
                </legend><?php foreach (['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $i => $label): ?><label
                        class="check"><input type="checkbox" name="days[]" value="<?= $i + 1 ?>"> <?= e($label) ?>
                    </label><?php endforeach; ?></fieldset>
            <button class="primary">Adicionar planejamento</button>
        </form>
    </section>
    <section class="card" id="exception"><h2>Exceção para uma data</h2>
        <form method="post" action="<?= e(url('exception-save')) ?>" data-ajax-form data-agenda-form><input type="hidden" name="_csrf"
                                                                            value="<?= e(csrf_token()) ?>"><label>Data<input
                        type="date" name="date" value="<?= e($_GET['date'] ?? $today->format('Y-m-d')) ?>"
                        required></label><label class="check"><input type="checkbox" name="cancel"> Cancelar o
                planejamento nessa data</label>
            <div class="exception-times"><label>Dormir<input type="time" name="bed"></label><label>Acordar<input
                            type="time" name="wake"></label></div>
            <button class="primary">Salvar exceção</button>
        </form>
    </section>
</div>
<section class="card" data-plan-list><h2>Padrões cadastrados</h2><?php if (!$plans): ?><p>Nenhum padrão ainda.</p><?php else: ?>
        <ul><?php foreach ($plans as $p): ?>
            <li><?= e(substr($p['hora_dormir'], 0, 5)) ?> → <?= e(substr($p['hora_acordar'], 0, 5)) ?> ·
            dias <?= e($p['dias'] ?? '') ?> · desde <?= e($p['valido_desde']) ?>
            <form class="inline" method="post" action="<?= e(url('plan-delete')) ?>" data-ajax-form data-agenda-form data-confirm="Remover este padrão?">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id"
                                                                                    value="<?= e($p['id']) ?>">
            <button class="link danger">Remover</button></form></li><?php endforeach; ?></ul><?php endif; ?></section>
<?php require __DIR__ . '/end.php'; ?>
