<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\showcase\Order;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\ShowcaseItem;
use Besnovatyj\Performance\services\showcase\DateRange;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $showcase Showcase */
/* @var $today DateTimeImmutable */
/* @var $range DateRange */
/* @var $performances Performance[] */
/* @var $items array<int, ShowcaseItem> ключ — id спектакля */
/* @var $publicIds int[] */
/* @var $candidates array<int, string> */

$rule = $showcase->rule();
$manualOrder = $rule->order === Order::Manual;
$public = array_flip($publicIds);

// Позиция на сайте — только у спектаклей, которые сайт покажет; отсечка по количеству — по ней.
$position = 0;
$rows = [];
foreach ($performances as $performance) {
    $item = $items[(int)$performance->id] ?? null;
    $isPublic = isset($public[(int)$performance->id]);
    $shown = $isPublic && ($item === null || $item->isActive());
    $rows[] = [
        'performance' => $performance,
        'item' => $item,
        'public' => $isPublic,
        'position' => $shown ? ++$position : null,
    ];
}
?>

<?= Html::beginTag('div', [
    'id' => 'showcase-items',
    'class' => 'card',
    'hx-target' => 'this',
    'hx-swap' => 'outerHTML',
]) ?>
    <div class="card-header d-md-flex justify-content-md-between align-items-center">
        <div>
            Спектакли: на сайте <?= $rule->limit !== null ? min($position, $rule->limit) : $position ?>
            <?php if ($rule->limit !== null && $position > $rule->limit): ?>
                <span class="text-secondary small">(подходит <?= $position ?>, выводится первых <?= $rule->limit ?>)</span>
            <?php endif; ?>
        </div>
        <?php if (!$showcase->isManual()): ?>
            <div class="text-secondary small">
                Отбор на <?= $today->format('d.m.Y') ?>
                <?php if ($range->isBounded()): ?>
                    · премьера <?= $range->from?->format('d.m.Y') ?? '…' ?> — <?= $range->to?->format('d.m.Y') ?? '…' ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($showcase->isManual()): ?>
        <div class="card-body border-bottom">
            <form class="d-flex flex-wrap gap-2" hx-post="<?= Url::to(['add-item']) ?>">
                <?= Html::hiddenInput('showcase_id', $showcase->id) ?>
                <?= Html::dropDownList('performance_id', null, $candidates, [
                    'class' => 'form-select w-auto flex-grow-1',
                    'prompt' => '— выберите спектакль —',
                    'required' => true,
                ]) ?>
                <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($range->warnings !== [] && !$showcase->isManual()): ?>
        <div class="card-body border-bottom">
            <?php foreach ($range->warnings as $warning): ?>
                <div class="text-danger small"><?= Html::encode($warning) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card-body p-0">
        <?php if ($rows === []): ?>
            <p class="text-secondary p-3 mb-0">
                <?= $showcase->isManual() ? 'В витрине пока нет спектаклей.' : 'Под правило сейчас не подходит ни один опубликованный спектакль.' ?>
            </p>
        <?php else: ?>
            <table class="table table-sm align-middle mb-0">
                <thead>
                <tr>
                    <th style="width: 3rem">#</th>
                    <th style="width: 90px">Фото</th>
                    <th>Спектакль</th>
                    <?php if ($manualOrder): ?>
                        <th style="width: 7rem">Место</th>
                    <?php endif; ?>
                    <th class="text-end">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <?= $this->render('_item', $row + [
                        'showcase' => $showcase,
                        'manualOrder' => $manualOrder,
                        'beyondLimit' => $rule->limit !== null && $row['position'] !== null && $row['position'] > $rule->limit,
                    ]) ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?= Html::endTag('div') ?>
