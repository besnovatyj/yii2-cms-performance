<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\DateTime\DateTimeWidget;
use Besnovatyj\Performance\entities\showcase\FromMode;
use Besnovatyj\Performance\entities\showcase\Order;
use Besnovatyj\Performance\entities\showcase\Snap;
use Besnovatyj\Performance\entities\showcase\Source;
use Besnovatyj\Performance\entities\showcase\ToMode;
use Besnovatyj\Performance\forms\backend\showcase\ShowcaseForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model ShowcaseForm */
/* @var $periodHtml string */

// Любое изменение поля пересчитывает период премьеры (HTMX, без сохранения).
// Отправку формы HTMX не перехватывает: у него свой триггер — change.
?>

<?php $form = ActiveForm::begin([
    'options' => [
        'hx-post' => Url::to(['period']),
        'hx-trigger' => 'change',
        'hx-target' => '#showcase-period',
        'hx-swap' => 'innerHTML',
    ],
]); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">Витрина</div>
                <div class="card-body">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'sort')->textInput(['type' => 'number']) ?>
                    <?= $form->field($model, 'source')->dropDownList(Source::options()) ?>
                    <?= $form->field($model, 'order_by')->dropDownList(Order::options()) ?>
                    <?= $form->field($model, 'items_limit')->textInput(['type' => 'number', 'min' => 1]) ?>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">Отбор (только для витрины «по правилу»)</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($model, 'taxonomy_id')->dropDownList($model->taxonomiesList(), ['prompt' => '— любой раздел —']) ?>
                        </div>
                        <div class="col-md-4 pt-md-4">
                            <?= $form->field($model, 'with_descendants')->checkbox() ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'from_mode')->dropDownList(FromMode::options()) ?>
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'from_date')->widget(DateTimeWidget::class, [
                                'showTime' => false,
                                'valueFormat' => 'Y-m-d',
                                'clearable' => true,
                                'options' => [
                                    'data-locale' => 'ru-RU',
                                    'data-week-starts-on' => '1',
                                ],
                            ])->hint('Для «с конкретной даты».') ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'from_offset_months')->textInput(['type' => 'number', 'min' => 0]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'from_snap')->dropDownList(Snap::options()) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'to_mode')->dropDownList(ToMode::options()) ?>

                    <div id="showcase-period" aria-live="polite"><?= $periodHtml ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
