<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\TreeManager\Manager\TreeDataSource;
use Besnovatyj\TreeManager\Manager\TreeWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var string $title
 * @var TreeDataSource $treeDataSource
 */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="performance-taxonomy-tree-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="btn-group">
            <?= Html::a(
                '<i class="bi bi-list-ul"></i> Список',
                ['/Performance/backend/taxonomy/index'],
                ['class' => 'btn btn-outline-secondary']
            ) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= TreeWidget::widget([
                'dataSource' => $treeDataSource,
                'endpoints' => [
                    'loadChildren' => Url::to(['/Performance/backend/taxonomy/load-children']),
                    'createNode' => Url::to(['/Performance/backend/taxonomy/create']),
                    'updateNode' => Url::to(['/Performance/backend/taxonomy/update']),
                    'deleteNode' => Url::to(['/Performance/backend/taxonomy/delete']),
                    'moveNode' => Url::to(['/Performance/backend/taxonomy/move']),
                    'toggleStatus' => Url::to(['/Performance/backend/taxonomy/toggle-status']),
                    'checkIntegrity' => Url::to(['/Performance/backend/taxonomy/check-integrity']),
                ],
                'serverForms' => [
                    'enabled' => true,
                    'display' => 'modal',
                    'errorStrategy' => 'both',
                    'operations' => [
                        'create' => true,
                        'edit' => true,
                    ],
                    'getFormUrl' => Url::to(['/Performance/backend/taxonomy/get-form']),
                ],
                'permissions' => [
                    'canCreate' => true, // Yii::$app->user->can('create'),
                    'canUpdate' => true, // Yii::$app->user->can('update'),
                    'canDelete' => true, // Yii::$app->user->can('delete'),
                    'canMove' => true, // Yii::$app->user->can('move'),
                ],
                'titleField' => 'title',
                'enablePersistence' => true,
                'storageKey' => 'performance-taxonomy-tree-state',
                'containerOptions' => [
                    'class' => 'performance-taxonomy-tree-widget',
                ],
            ]) ?>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
.performance-taxonomy-tree-widget {
    min-height: 400px;
}
CSS
);
?>
