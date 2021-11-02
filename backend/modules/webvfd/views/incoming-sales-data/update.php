<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\IncomingSalesData */

$this->title = Yii::t('app', 'Update Incoming Sales Data: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Incoming Sales Datas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="incoming-sales-data-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
