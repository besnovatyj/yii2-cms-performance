<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\season\Season;
use Besnovatyj\Performance\forms\backend\season\SeasonForm;
use yii\web\View;

/* @var $this View */
/* @var $model SeasonForm */
/* @var $season Season */

$this->title = 'Сезон ' . $season->name;
$this->params['breadcrumbs'][] = ['label' => 'Театральные сезоны', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_form', [
    'model' => $model,
]) ?>
