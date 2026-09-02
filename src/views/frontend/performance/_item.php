<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model Performance */

$url = Url::to(['view', 'id' => $model->id]);

?>

<div class="card h-100 shadow-sm">
    <img src="<?= $model->mainImage->getThumbUrl('file', 'frontend_list') ?>" class="card-img-top"
         alt="<?= Html::encode($model->title) ?>"
         title="<?= Html::encode($model->title) ?>">
    <div class="card-body d-flex flex-column">
        <h2 class="h5 card-title"><?= Html::encode($model->title) ?></h2>
        <?php if (!empty($model->premiere_date)): ?>
            <div class="text-secondary small mb-2">
                Премьера: <?= Yii::$app->formatter->asDate($model->premiere_date, 'long') ?>
            </div>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php if (isset($model->taxonomy->name)): ?>
                <span class="badge text-bg-secondary"><?= Html::encode($model->taxonomy->name) ?></span>
            <?php endif; ?>
            <?php if (!empty($model->age_limit)): ?>
                <span class="badge text-bg-light border"><?= Html::encode($model->age_limit) ?></span>
            <?php endif; ?>
        </div>
        <a href="<?= $url ?>" class="btn btn-primary mt-auto align-self-start">Подробнее</a>
    </div>
</div>
