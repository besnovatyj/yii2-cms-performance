<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\forms\backend\TagForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $model TagForm */
/* @var $form ActiveForm */
?>

<?php $form = ActiveForm::begin(); ?>
<div class="card">
    <div class="card-header"><?= $this->title ?></div>
    <!-- /.card-header -->
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class'=>'form-control']) ?>
        <?= $form->field($model, 'slug')->textInput(['maxlength' => true, 'class'=>'form-control']) ?>
    </div>
    <!-- /.card-body -->
    <div class="card-footer clearfix">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
</div>
<!-- /.card -->
<?php ActiveForm::end(); ?>
