<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\forms\backend\performance\PerformanceForm;
use yii\web\View;

/* @var $this View */
/* @var $performance Performance */
/* @var $model PerformanceForm */

$this->title = 'Update performance: ' . $performance->title;
$this->params['breadcrumbs'][] = ['label' => 'Performances', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $performance->title, 'url' => ['view', 'id' => $performance->id]];
$this->params['breadcrumbs'][] = 'Update';

echo $this->render('_form', [
    'model' => $model,
    'performance' => $performance,
]);
