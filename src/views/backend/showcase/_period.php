<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\services\showcase\DateRange;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $manual bool */
/* @var $today DateTimeImmutable */
/* @var $range DateRange */

$format = static fn(?DateTimeImmutable $date): string => $date?->format('d.m.Y') ?? '…';
?>

<div class="alert <?= $range->isEmpty() ? 'alert-danger' : 'alert-secondary' ?> mb-0">
    <?php if ($manual): ?>
        Ручная витрина: раздел и период премьеры не действуют, выводятся добавленные спектакли.
    <?php elseif (!$range->isBounded()): ?>
        Период премьеры не ограничен.
    <?php else: ?>
        Период премьеры на сегодня (<?= $today->format('d.m.Y') ?>):
        <strong><?= $format($range->from) ?> — <?= $format($range->to) ?></strong>
        <?php if ($range->isEmpty()): ?>
            <div>Нижняя граница позже верхней — витрина будет пустой.</div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!$manual): ?>
        <?php foreach ($range->warnings as $warning): ?>
            <div class="text-danger small mt-1"><?= Html::encode($warning) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
