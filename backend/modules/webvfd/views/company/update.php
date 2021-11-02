<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\Company */

$this->title = Yii::t('app', 'Update Company: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-update">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
