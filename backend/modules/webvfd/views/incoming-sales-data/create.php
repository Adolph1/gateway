<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\IncomingSalesData */

$this->title = Yii::t('app', 'Create Incoming Sales Data');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Incoming Sales Datas'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="incoming-sales-data-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
