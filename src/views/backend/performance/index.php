<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\forms\backend\search\PerformanceSearch;
use Besnovatyj\Performance\helpers\PerformanceHelper;
use Besnovatyj\Backend\Widgets\pagination\LinkPager;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel PerformanceSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Performances';
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a('Create', ['create'], ['class' => 'btn  btn-success']) ?>
</p>

<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => "{summary}\n{items}",
            'columns' => [
                [
                    'value' => static function (Performance $model) {
                        return $model->mainImage ? Html::img($model->mainImage->getThumbUrl('file', 'admin')) : null;
                    },
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'width: 100px'],
                ],
                'id',
                [
                    'attribute' => 'title',
                    'value' => static function (Performance $model) {
                        return Html::a(Html::encode($model->title), ['view', 'id' => $model->id]);
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'taxonomy_id',
                    'filter' => $searchModel->taxonomiesList(),
                    'value' => 'taxonomy.name',
                ],
                [
                    'attribute' => 'premiere_date',
                    'format' => 'date',
                ],
                [
                    'attribute' => 'status',
                    'filter' => $searchModel->statusList(),
                    'value' => function (Performance $model) {
                        return PerformanceHelper::statusLabel($model);
                    },
                    'format' => 'raw',
                ],
            ],
        ]); ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <nav aria-label="" class="nav-pagination">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->getPagination(),
            ]) ?>
        </nav>
    </div>
</div>
<!-- /.card -->
