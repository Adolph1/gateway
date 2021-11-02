<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'List of Companies';
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="user-index" style="font-size: 0.8em">
    <p>
        <?= Html::a('Create', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?php $gridColumns = [
        [
            'class' => 'yii\grid\SerialColumn',

        ],
        [
            'attribute' => 'status',
           // 'filterType' => GridView::FILTER_SELECT2,
            //'filter' => [0 => 'DISABLED', 1 => 'NOT ACTIVATED', 2 => 'ACTIVATED'],
            //'filter' => \yii\helpers\ArrayHelper::map(\frontend\models\Status::find()->asArray()->all(), 'id', 'name'),
            'filter' => \backend\modules\webvfd\models\Company::getCompanyStatus(),

            'filterInputOptions' => ['placeholder' => 'Search'],
            'format' => 'raw',
            'value' => function ($model) {
                if (!empty($model->status)) {
                    return $model->statusLabel;
                }
            }
        ],
        'name',
        //'company_username',
        'tin',
        'vrn',
       // 'certificate_serial',
        'serial_number',
        'uin',
        //'tax_office',
       // 'address',
       // 'email:email',
       // 'business_licence',
        'contact_person',
//        [
//            'attribute' => 'company_id_type',
//            'value' => 'companyType.name'
//        ],


        //'create_by',
        //'created_at',
        //'updated_at',
        [
            'class' => 'yii\grid\ActionColumn',
            'header' => 'Actions',
            'template' => '{view}',
            // 'visible' => yii::$app->user->can('applyForUinCertificate'),
            'buttons' => [
                'view' => function ($url, $model) {
                    $url = ['view', 'id' => $model->id];
                    return Html::a('<span class="fa fa-eye"></span>', $url, [
                        'title' => 'View',
                        'data-toggle' => 'tooltip', 'data-original-title' => 'Save',
                        'class' => 'btn btn-primary',

                    ]);


                },

            ]
        ],


    ];


    // the GridView widget (you must use kartik\grid\GridView)
    echo GridView::widget([
        'dataProvider' => $dataProvider,
       // 'filterModel' => $searchModel,
        'columns' => $gridColumns,
        'id' => 'grid',
    //    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
     /*   'beforeHeader' => [
            [
                'options' => ['class' => 'skip-export'] // remove this row from export
            ]
        ],*/

       // 'pjax' => true,
       // 'bordered' => true,
      //  'striped' => true,
      //  'condensed' => true,
     //   'responsive' => true,
      //  'hover' => true,
        //   'floatHeader' => true,

        //   'floatHeaderOptions' => ['scrollingTop' => true],
       // 'showPageSummary' => true,

        'rowOptions' => function ($model) {
            return ['data-id' => $model->id];
        },


    ]); ?>

</div>





