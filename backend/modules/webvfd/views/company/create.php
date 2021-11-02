<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\modules\webvfd\models\Company */

$this->title = Yii::t('app', 'NEW COMPANY');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
