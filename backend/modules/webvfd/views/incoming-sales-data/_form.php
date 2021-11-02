<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\IncomingSalesData */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="incoming-sales-data-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sales_data')->textarea(['rows' => 6]) ?>


    <?= $form->field($model, 'qrCode')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'date_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
