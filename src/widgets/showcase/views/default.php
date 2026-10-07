<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\readModels\views\ShowcaseCard;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $showcase Showcase */
/* @var $cards ShowcaseCard[] */
/* @var $thumbProfile string */

// Базовая разметка пакета: сетка карточек Bootstrap 5. Заголовок секции — забота страницы.
?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4" data-showcase="<?= Html::encode($showcase->code) ?>">
    <?php foreach ($cards as $card): ?>
        <?php
        $performance = $card->performance;
        $url = Url::to(['/Performance/performance/view', 'id' => $performance->id]);
        ?>
        <div class="col">
            <div class="card h-100">
                <?php if ($card->image !== null): ?>
                    <a href="<?= $url ?>">
                        <img src="<?= $card->image->getThumbUrl('file', $thumbProfile) ?>" class="card-img-top"
                             alt="<?= Html::encode($performance->title) ?>" loading="lazy">
                    </a>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h3 class="h5 card-title">
                        <a href="<?= $url ?>" class="stretched-link text-reset text-decoration-none"><?= Html::encode($performance->title) ?></a>
                    </h3>
                    <?php if (!empty($performance->premiere_date)): ?>
                        <div class="text-body-secondary small mb-2">
                            Премьера: <?= Yii::$app->formatter->asDate($performance->premiere_date, 'long') ?>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        <?php if ($performance->taxonomy !== null): ?>
                            <span class="badge text-bg-secondary"><?= Html::encode($performance->taxonomy->name) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($performance->age_limit)): ?>
                            <span class="badge text-bg-light border"><?= Html::encode($performance->age_limit) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
