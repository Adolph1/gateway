
<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin(); ?>

<div class="user-form">
    <div class="row">
        <div class="col-sm-2">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-block btn-success' : 'btn btn-block btn-info']) ?>
        </div>
        <div class="col-sm-2">
            <?= Html::a(Yii::t('app', 'Cancel'), ['index'], ['class' => 'btn btn-warning btn-block']) ?>
        </div>
    </div>
    <hr>
    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="col-sm-4">
                    <?= $form->field($model, 'tin', ['enableAjaxValidation' => true])->textInput(['autofocus' => true,'placeholder'=>'Eg:- 000000001']) ?>

                </div>
                <div class="col-sm-4">
                    <?= $form->field($model, 'serial_number', ['enableAjaxValidation' => true])->textInput(['autofocus' => true,'placeholder'=>'Eg:- 10TZ100523']) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model, 'certificate_password', ['enableAjaxValidation' => true])->textInput(['autofocus' => true,]) ?>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?= $form->field($model, 'certificate_serial')->textInput(['maxlength' => true, 'placeholder'=>'Eg:- 24355c01f058d9b740115a9697b485d8']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model, 'business_licence')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-4">
                    <?= $form->field($model, 'company_id_type')->dropDownList(\backend\modules\webvfd\models\CompanyType::getCompanyType(),['prompt'=>'Select Type']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <?= $form->field($model, 'company_username')->textInput(['maxlength' => true]) ?>
                </div>

                <div class="col-sm-4">
                    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
                </div>

                <div class="col-sm-4">
                    <?= $form->field($model, 'pfx_file')->fileInput() ?>
                </div>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>
</div>



