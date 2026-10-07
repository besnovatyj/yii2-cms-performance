<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Backend\Widgets\grid\ActionColumn;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Театральные сезоны';
$this->params['breadcrumbs'][] = ['label' => 'Витрины спектаклей', 'url' => ['/Performance/backend/showcase/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p class="d-flex flex-wrap align-items-center gap-2">
    <?= Html::a('Добавить сезон', ['create'], ['class' => 'btn btn-success']) ?>
</p>

<div class="card">
    <div class="card-header"><?= Html::encode($this->title) ?></div>
    <div class="card-body">
        <p class="text-secondary small">
            От сезонов витрины отсчитывают периоды «к открытию сезона» и «по закрытие сезона».
            Дата в межсезонье относится к закрывшемуся сезону, пока не откроется следующий.
        </p>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{summary}\n{items}",
            'columns' => [
                'name:text:Название',
                'start_date:date:Открытие',
                'end_date:date:Закрытие',
                [
                    'class' => ActionColumn::class,
                    'template' => '{update} {delete}',
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
