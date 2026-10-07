<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\showcase\Showcase;
use Besnovatyj\Performance\forms\backend\showcase\ShowcaseForm;
use yii\web\View;

/* @var $this View */
/* @var $model ShowcaseForm */
/* @var $showcase Showcase */
/* @var $periodHtml string */

$this->title = 'Редактировать: ' . $showcase->name;
$this->params['breadcrumbs'][] = ['label' => 'Витрины спектаклей', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $showcase->name, 'url' => ['view', 'id' => $showcase->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<?= $this->render('_form', [
    'model' => $model,
    'periodHtml' => $periodHtml,
]) ?>
