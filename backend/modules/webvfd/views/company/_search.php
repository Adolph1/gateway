<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\CompanySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'tin') ?>

    <?= $form->field($model, 'certificate_password') ?>

    <?= $form->field($model, 'certificate_serial') ?>

    <?php // echo $form->field($model, 'vrn') ?>

    <?php // echo $form->field($model, 'serial_number') ?>

    <?php // echo $form->field($model, 'receipt_number') ?>

    <?php // echo $form->field($model, 'receipt_v_number') ?>

    <?php // echo $form->field($model, 'registration_id') ?>

    <?php // echo $form->field($model, 'uin') ?>

    <?php // echo $form->field($model, 'tax_office') ?>

    <?php // echo $form->field($model, 'address') ?>

    <?php // echo $form->field($model, 'street') ?>

    <?php // echo $form->field($model, 'city') ?>

    <?php // echo $form->field($model, 'country') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'business_licence') ?>

    <?php // echo $form->field($model, 'contact_person') ?>

    <?php // echo $form->field($model, 'company_id_type') ?>

    <?php // echo $form->field($model, 'file') ?>

    <?php // echo $form->field($model, 'company_username') ?>

    <?php // echo $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'reg_status') ?>

    <?php // echo $form->field($model, 'approved_by') ?>

    <?php // echo $form->field($model, 'approved_at') ?>

    <?php // echo $form->field($model, 'create_by') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
