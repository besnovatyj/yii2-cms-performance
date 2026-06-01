<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\forms\backend\performance\PerformanceForm;
use yii\web\View;

/* @var $this View */
/* @var $model PerformanceForm */

$this->title = 'Create';
$this->params['breadcrumbs'][] = ['label' => 'Performances', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form', ['model' => $model]);
