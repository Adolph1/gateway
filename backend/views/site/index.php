<?php

/* @var $this yii\web\View */

$this->title = 'DASHBOARD';
?>
<div class="site-index">

    <div class="row">
        <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12">
<!--            --><?php
//            $properties = \backend\models\Property::find()->all();
//            $plans = array();
//            $data = array();
//            if($properties != null){
//                foreach ($properties as $property){
//                    $plans[] = [
//                        'name' => $property->name,
//                        'data' => \backend\models\Plan::getPlans($property->id),
//                    ];
//                }
//            }
//            $item_key_data = array_keys($plans);
//            $itemsArraySize = count($plans);
//
//            for($i=0; $i< $itemsArraySize; $i++){
//                $data[] = $plans[$i];
//            }
//
//
//
//
//            echo \dosamigos\highcharts\HighCharts::widget([
//                'clientOptions' => [
//                    'chart' => [
//                        'type' => 'line'
//                    ],
//                    'title' => [
//                        'text' => 'Plans per property per year'
//                    ],
//                    'xAxis' => [
//                        'categories' => [
//                            'Jan',
//                            'Feb',
//                            'Mar',
//                            'Apr',
//                            'May',
//                            'Jun',
//                            'Jul',
//                            'Aug',
//                            'Sep',
//                            'Oct',
//                            'Nov',
//                            'Dec',
//                        ]
//                    ],
//                    'yAxis' => [
//                        'title' => [
//                            'text' =>'Total'
//                        ]
//                    ],
//                    'series' => $data
//                ]
//            ]);
//            ?>
        </div>
    </div>


    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">

            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-12 col-sm-6 col-md-3">

            <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <!-- fix for small devices only -->
        <div class="clearfix hidden-md-up"></div>



    </div>


</div>
