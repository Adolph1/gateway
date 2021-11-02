<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\modules\webvfd\models\IncomingSalesDataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Incoming Sales Datas');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="incoming-sales-data-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],


            'date_time',
            [
                'attribute' => 'company_id',
                'vAlign' => 'middle',
                'width' => '100px',

                'filterType' => GridView::FILTER_SELECT2,
                'filter' => ArrayHelper::map(\backend\modules\webvfd\models\Company::find()->orderBy('name')->asArray()->all(), 'id', 'name'),
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                    //'options' => ['multiple' => true]
                ],
                'filterInputOptions' => ['placeholder' => 'Search company'],

                'value' => function($model){
                    if($model->company_id != null) {
                        return $model->myCompany->name;
                    }else{
                        return '';
                    }
                }
            ],
            'sales_data:ntext',

           [
                   'attribute' =>  'status',
                    'value' => function($model){
                    return $model->statusLabel;
                    }
           ]


//            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
