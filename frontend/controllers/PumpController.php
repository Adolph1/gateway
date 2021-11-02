<?php

namespace frontend\controllers;

use frontend\models\PtsCommand;
use Ripoo\OdooClient;
use Yii;
use frontend\models\Pump;
use frontend\models\PumpSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Url;

/**
 * PumpController implements the CRUD actions for Pump model.
 */
class PumpController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Pump models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PumpSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionViewStatus()
    {
        $model = PtsCommand::GetPumps();

        return $this->render('status', [
            'model' => $model,
        ]);
    }

    public function actionConnect()
    {
        $host = 'http://192.241.129.238:8069';
        $db = 'zccl_db';
        $user = 'adolph.cm@gmail.com';
        $password = '123456';

        $client = new OdooClient($host, $db, $user, $password);

        if($client){
//            $data = [
//                'name' => 'Adolph Doe Zungu',
//                'email' => 'adolph@odoo.com',
//            ];
//
//            $id = $client->create('res.partner', $data);



            $host = 'http://192.241.129.238:8069';
            $db = 'zrb_erp';
            $user = 'adolph.cm@gmail.com';
            $password = 'password1,';

            $client = new OdooClient($host, $db, $user, $password);
            $line_vals = [
                'product_id' => 1,
                'name' => 'test',
                'product_uom_qty' => 10,
                'price_unit'=> 30000
            ];
            $order_vals = [
                'partner_id' => 1,
                'validity_date' => date('Y-m-d H:i:s'),
                'order_line' => [[0, 0, $line_vals]],
            ];

            $id = $client->create('sale.order', $order_vals);


            if($id){
                echo $id;
            }
        }else{
            return 'False';
        }
    }



    /**
     * Displays a single Pump model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Pump model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Pump();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Pump model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Pump model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Pump model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Pump the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pump::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
