<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\forms\backend\showcase\ShowcaseForm;
use yii\web\View;

/* @var $this View */
/* @var $model ShowcaseForm */
/* @var $periodHtml string */

$this->title = 'Создать витрину';
$this->params['breadcrumbs'][] = ['label' => 'Витрины спектаклей', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
    'model' => $model,
    'periodHtml' => $periodHtml,
]) ?>
