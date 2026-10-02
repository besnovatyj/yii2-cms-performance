<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $url string */
/* @var $label string */
?>
<div class="text-center my-4">
    <?= Html::a(Html::encode($label), $url, [
        'class' => 'btn btn-primary btn-lg',
        'target' => '_blank',
        'rel' => 'noopener',
    ]) ?>
</div>
