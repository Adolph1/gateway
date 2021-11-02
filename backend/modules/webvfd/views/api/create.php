<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\WebVfdApi */

$this->title = Yii::t('app', 'Create Web Vfd Api');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Web Vfd Apis'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="web-vfd-api-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
