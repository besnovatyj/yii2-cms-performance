<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\showcase\FromMode;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\Snap;
use Besnovatyj\Performance\entities\showcase\ToMode;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $showcase Showcase */
/* @var $itemsHtml string */

$this->title = $showcase->name;
$this->params['breadcrumbs'][] = ['label' => 'Витрины спектаклей', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$rule = $showcase->rule();
$from = match ($rule->fromMode) {
    FromMode::None => 'без ограничения',
    FromMode::Fixed => 'с ' . $rule->fromDate?->format('d.m.Y'),
    FromMode::Relative => $rule->fromOffsetMonths . ' мес. назад от сегодня'
        . ($rule->fromSnap !== Snap::None ? ', ' . mb_strtolower($rule->fromSnap->label()) : ''),
};
?>

<p class="d-flex flex-wrap align-items-center gap-2">
    <?php if ($showcase->isActive()): ?>
        <?= Html::a('Выключить', ['draft', 'id' => $showcase->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
    <?php else: ?>
        <?= Html::a('Включить', ['activate', 'id' => $showcase->id], ['class' => 'btn btn-success', 'data-method' => 'post']) ?>
    <?php endif; ?>
    <?= Html::a('Редактировать', ['update', 'id' => $showcase->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Удалить', ['delete', 'id' => $showcase->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => 'Удалить витрину вместе с её настройками спектаклей?',
            'method' => 'post',
        ],
    ]) ?>
</p>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Витрина</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Код</th><td><code><?= Html::encode($showcase->code) ?></code></td></tr>
                    <tr><th>Статус</th><td><?= $showcase->isActive()
                                ? Html::tag('span', 'Вкл', ['class' => 'badge bg-success'])
                                : Html::tag('span', 'Выкл', ['class' => 'badge bg-secondary']) ?></td></tr>
                    <tr><th>Спектакли</th><td><?= Html::encode($rule->source->label()) ?></td></tr>
                    <tr><th>Порядок</th><td><?= Html::encode($rule->order->label()) ?></td></tr>
                    <tr><th>Сколько выводить</th><td><?= $rule->limit ?? 'все' ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Вызов</div>
            <div class="card-body">
                <p class="mb-1">В теме:</p>
                <pre class="mb-3"><code>&lt;?= ShowcaseWidget::widget(['code' =&gt; '<?= Html::encode($showcase->code) ?>']) ?&gt;</code></pre>
                <p class="mb-1">Шорткодом (имя — как зарегистрировано в модуле шорткодов):</p>
                <pre class="mb-0"><code>[performanceShowcase code=<?= Html::encode($showcase->code) ?>]</code></pre>
            </div>
        </div>
    </div>
</div>

<?php if (!$showcase->isManual()): ?>
    <div class="card mb-3">
        <div class="card-header">Отбор</div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr><th>Раздел</th><td><?= $showcase->taxonomy
                            ? Html::encode($showcase->taxonomy->name) . ($rule->withDescendants ? ' (с подразделами)' : '')
                            : 'любой' ?></td></tr>
                <tr><th>Премьера от</th><td><?= Html::encode($from) ?></td></tr>
                <tr><th>Премьера до</th><td><?= Html::encode(mb_strtolower($rule->toMode === ToMode::None ? 'без ограничения' : $rule->toMode->label())) ?></td></tr>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= $itemsHtml ?>
