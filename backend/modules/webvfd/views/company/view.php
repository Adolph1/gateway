<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model \backend\modules\webvfd\models\Company */

$this->title = Yii::t('app', Html::encode($model->name) . ' COMPANY DETAILS');
$this->params['breadcrumbs'][] = ['label' => 'Companies', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="company-view">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Back home', ['index'], ['class' => 'btn btn-warning']) ?>
        <?php if($model->reg_status == \backend\modules\webvfd\models\Company::NOT_REGISTERED) {
            echo Html::a('Delete', ['delete', 'id' => $model->id], [

                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]);
        }?>
        <?php if($model->reg_status == \backend\modules\webvfd\models\Company::ALREADY_REGISTERED) {
            echo Html::a('Get Token', ['get-token', 'id' => $model->id], [

                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'Are you sure you want to get token?',
                    'method' => 'post',
                ],
            ]);
        }?>
<?php if($model->reg_status == \backend\modules\webvfd\models\Company::NOT_REGISTERED){ ?>
        <?= Html::a('Register To EFDMS', ['register', 'id' => $model->id], [
            'class' => 'btn btn-info',
            'data' => [
                'confirm' => 'Are you sure you want to register this company to WEB EFDMS?',
                'method' => 'post',
            ],
        ]) ?>
        <?php }?>
    </p>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //  'id',
            'name',
             'company_username',
            'tin',
            'vrn',
            'certificate_serial',
                'serial_number',
            'uin',
            'tax_office',
            'address',
            'street',
            'city',
            'country',
            'email:email',
            'business_licence',
            'contact_person',
            [
                'attribute' => 'company_id_type',
                'value' => $model->companyType->name
            ],
            [
                'attribute' => 'status',
                'value' => $model->statusLabel
            ],
            'create_by',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
