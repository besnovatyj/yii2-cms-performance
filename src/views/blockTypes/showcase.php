<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use Besnovatyj\Blocks\render\BlockViewContext;
use Besnovatyj\Performance\widgets\showcase\ShowcaseWidget;
use yii\helpers\Html;
use yii\web\View;

/**
 * Блок «Витрина спектаклей» (тип `Performance:showcase` модуля блоков), шаблон пакета.
 *
 * Нейтральная разметка Bootstrap: заголовок, витрина виджетом пакета, ссылка «вся афиша». Пакет
 * умеет только сетку, поэтому раскладка из блока здесь не используется — её применяет тема,
 * перекрывая этот файл: `themes/{тема}/modules/Performance/views/blockTypes/showcase.php`.
 *
 * Пустая витрина (выключена, удалена, нет спектаклей в периоде) — блок не выводится вовсе, даже
 * заголовок: раздел без содержимого на главной хуже, чем его отсутствие.
 */

/** @var View $this */
/** @var array{heading:string, showcase:string, layout:string, limit:string, more:array, moreLabel:string} $data */
/** @var BlockViewContext $block */

$cards = ShowcaseWidget::widget([
    'code' => $data['showcase'],
    'limit' => $data['limit'] !== '' ? (int)$data['limit'] : null,
]);
if ($cards === '') {
    return;
}

$moreUrl = $block->linkUrl($data['more']);
?>
<section class="block-showcase">
    <?php if ($data['heading'] !== ''): ?>
        <h2><?= Html::encode($data['heading']) ?></h2>
    <?php endif; ?>
    <?= $cards ?>
    <?php if ($moreUrl !== '' && $data['moreLabel'] !== ''): ?>
        <p class="mt-3"><?= Html::a(Html::encode($data['moreLabel']), $moreUrl) ?></p>
    <?php endif; ?>
</section>
