<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Image;
use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\ShowcaseItem;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $showcase Showcase */
/* @var $performance Performance */
/* @var $item ShowcaseItem|null */
/* @var $public bool сайт показывает спектакль (опубликован он сам и его раздел) */
/* @var $position int|null место на сайте; null — не выводится */
/* @var $manualOrder bool */
/* @var $beyondLimit bool */

$hidden = $item !== null && !$item->isActive();
$chosenId = $item?->image_id !== null ? (int)$item->image_id : null;

/** @var Image|null $shownImage */
$shownImage = $performance->mainImage;
foreach ($performance->images as $image) {
    if ((int)$image->id === $chosenId) {
        $shownImage = $image;
    }
}

$imageButton = static function (?Image $image, bool $active) {
    $content = $image
        ? Html::img($image->getThumbUrl('file', 'admin'), ['alt' => '', 'width' => 49, 'height' => 70, 'class' => 'd-block'])
        : 'Главное';
    return Html::button($content, [
        'class' => 'btn btn-sm p-1 ' . ($active ? 'btn-primary' : 'btn-outline-secondary'),
        'title' => $image ? 'Показывать это фото' : 'Показывать главное фото спектакля',
        'hx-post' => Url::to(['choose-image']),
        'hx-vals' => Json::encode(['image_id' => $image?->id ?? '']),
    ]);
};
?>

<?= Html::beginTag('tr', [
    'class' => ($hidden || !$public || $beyondLimit) ? 'opacity-50' : null,
    'hx-vals' => Json::encode(['showcase_id' => (int)$showcase->id, 'performance_id' => (int)$performance->id]),
]) ?>
    <td><?= $position ?? '—' ?></td>
    <td>
        <?= $shownImage
            ? Html::img($shownImage->getThumbUrl('file', 'admin'), ['alt' => '', 'width' => 70, 'height' => 100])
            : Html::tag('span', 'нет фото', ['class' => 'text-secondary small']) ?>
    </td>
    <td>
        <div><?= Html::a(Html::encode($performance->title), ['/Performance/backend/performance/view', 'id' => $performance->id]) ?></div>
        <div class="text-secondary small">
            <?= $performance->premiere_date ? 'Премьера ' . Yii::$app->formatter->asDate($performance->premiere_date, 'php:d.m.Y') : 'Без даты премьеры' ?>
            <?= $performance->taxonomy ? ' · ' . Html::encode($performance->taxonomy->name) : '' ?>
        </div>
        <div class="d-flex flex-wrap gap-1 mt-1">
            <?php if ($hidden): ?>
                <span class="badge bg-secondary">скрыт в витрине</span>
            <?php endif; ?>
            <?php if (!$public): ?>
                <span class="badge bg-warning text-dark">не опубликован на сайте</span>
            <?php endif; ?>
            <?php if ($beyondLimit): ?>
                <span class="badge bg-light text-dark border">за пределами количества</span>
            <?php endif; ?>
            <?php if ($chosenId !== null): ?>
                <span class="badge bg-info text-dark">своё фото</span>
            <?php endif; ?>
        </div>
        <?php if (count($performance->images) > 0): ?>
            <details class="mt-1">
                <summary class="small">Фото для витрины (<?= count($performance->images) ?>)</summary>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <?= $imageButton(null, $chosenId === null) ?>
                    <?php foreach ($performance->images as $image): ?>
                        <?= $imageButton($image, (int)$image->id === $chosenId) ?>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endif; ?>
    </td>
    <?php if ($manualOrder): ?>
        <td>
            <?= Html::input('number', 'sort', $item?->sort, [
                'class' => 'form-control form-control-sm',
                'placeholder' => 'авто',
                'title' => 'Меньше — выше. Пусто — после спектаклей с заданным местом.',
                'hx-post' => Url::to(['move-item']),
                'hx-trigger' => 'change',
            ]) ?>
        </td>
    <?php endif; ?>
    <td class="text-end text-nowrap">
        <?= Html::button($hidden ? 'Показать' : 'Скрыть', [
            'class' => 'btn btn-sm btn-outline-secondary',
            'hx-post' => Url::to(['toggle-item']),
        ]) ?>
        <?php if ($showcase->isManual()): ?>
            <?= Html::button('Убрать', [
                'class' => 'btn btn-sm btn-outline-danger',
                'hx-post' => Url::to(['remove-item']),
                'hx-confirm' => 'Убрать «' . $performance->title . '» из витрины?',
            ]) ?>
        <?php elseif ($item !== null): ?>
            <?= Html::button('Сбросить', [
                'class' => 'btn btn-sm btn-outline-danger',
                'title' => 'Вернуть главное фото, место по порядку и показ',
                'hx-post' => Url::to(['remove-item']),
            ]) ?>
        <?php endif; ?>
    </td>
<?= Html::endTag('tr') ?>
