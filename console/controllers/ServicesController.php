<?php

namespace console\controllers;

use backend\models\Building;
use yii\console\Controller;
ini_set('memory_limit','5048M');

/**
 * ServicesController implements the CRUD actions for SmsController model.
 */
class ServicesController extends Controller
{

    public function actionBuildingPlanning()
    {
        //voucher planning everyday till the voucher is closed
        Buil::planEligible();
    }




}
