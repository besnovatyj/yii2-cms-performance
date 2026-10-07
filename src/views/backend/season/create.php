<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\forms\backend\season\SeasonForm;
use yii\web\View;

/* @var $this View */
/* @var $model SeasonForm */

$this->title = 'Добавить сезон';
$this->params['breadcrumbs'][] = ['label' => 'Театральные сезоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
    'model' => $model,
]) ?>
