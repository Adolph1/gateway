<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\WebVfdApi */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="web-vfd-api-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'request_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'request_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'maker')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'maker_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
