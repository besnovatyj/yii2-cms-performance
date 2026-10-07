<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\DateTime\DateTimeWidget;
use Besnovatyj\Performance\forms\backend\season\SeasonForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model SeasonForm */

$dateWidget = [
    'showTime' => false,
    'valueFormat' => 'Y-m-d',
    'options' => [
        'data-locale' => 'ru-RU',
        'data-week-starts-on' => '1',
    ],
];
?>

<?php $form = ActiveForm::begin(); ?>

<div class="card">
    <div class="card-header">Сезон</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'start_date')->widget(DateTimeWidget::class, $dateWidget) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'end_date')->widget(DateTimeWidget::class, ['clearable' => true] + $dateWidget) ?>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-grid gap-2">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
