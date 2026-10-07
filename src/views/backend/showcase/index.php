<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\entities\showcase\Source;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Витрины спектаклей';
$this->params['breadcrumbs'][] = $this->title;
?>

<p class="d-flex flex-wrap align-items-center gap-2">
    <?= Html::a('Создать витрину', ['create'], ['class' => 'btn btn-success']) ?>
    <?= Html::a('Сезоны', ['/Performance/backend/season/index'], ['class' => 'btn btn-outline-secondary']) ?>
</p>

<div class="card">
    <div class="card-header"><?= Html::encode($this->title) ?></div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{summary}\n{items}",
            'columns' => [
                'id',
                [
                    'attribute' => 'name',
                    'value' => static fn(Showcase $model): string => Html::a(Html::encode($model->name), ['view', 'id' => $model->id]),
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'code',
                    'value' => static fn(Showcase $model): string => Html::tag('code', Html::encode($model->code)),
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'source',
                    'label' => 'Спектакли',
                    'value' => static fn(Showcase $model): string => (Source::tryFrom((string)$model->source) ?? Source::Rule)->label()
                        . ($model->taxonomy ? ' · ' . $model->taxonomy->name : ''),
                ],
                [
                    'attribute' => 'status',
                    'value' => static fn(Showcase $model): string => $model->isActive()
                        ? Html::tag('span', 'Вкл', ['class' => 'badge bg-success'])
                        : Html::tag('span', 'Выкл', ['class' => 'badge bg-secondary']),
                    'format' => 'raw',
                ],
                'sort',
                [
                    'class' => ActionColumn::class,
                    'template' => '{view} {update} {delete}',
                ],
            ],
        ]) ?>
    </div>
    <div class="card-footer clearfix">
        <nav aria-label="" class="nav-pagination">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ]) ?>
        </nav>
    </div>
</div>
