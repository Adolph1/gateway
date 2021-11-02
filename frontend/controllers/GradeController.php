<?php

namespace frontend\controllers;

use frontend\models\Pump;
use Yii;
use frontend\models\Grade;
use frontend\models\GradeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GradeController implements the CRUD actions for Grade model.
 */
class GradeController extends Controller
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
     * Lists all Grade models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new GradeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionViewPrices()
    {
        $model = Grade::find()->all();

        return $this->render('change_price', [
            'model' => $model,
        ]);
    }


    public function actionChangePrice()
    {


        $model = new Grade();
        try {

            if ($model->load(Yii::$app->request->post())) {
                $grades = Yii::$app->request->post();
                $GradeID = $grades['Grade'];

                foreach ($GradeID as $key => $items) {
                    $price = $items['price'];
                    $GradeArray[] = ["Id" => $key,
                        "Name" => Grade::getNameById($key),
                        "Price" => (float)$price,
                        "ExpansionCoefficient" => (float)Grade::geCoefficientById($key),
                    ];
                }

                $data = [
                    'Protocol' => 'jsonPTS',
                    'Packets' => array([
                        "Id" => 1,
                        "Type" => "SetFuelGradesConfiguration",
                        "Data" => [
                            "FuelGrade" => $GradeArray,
                        ],
                    ])
                ];

                // return Json
                $username = 'admin';
                $password = 'admin';
                $url = "http://192.168.1.117/jsonPTS";
                $headers = array(
                    "Content-Type: application/json; charset=utf-8",
                    "Accept: application/json",
                    "Authorization: Basic " . base64_encode($username . ":" . $password),
                );
                $datastring = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);
                $result = curl_exec($curl);
                curl_close($curl);
                if($result) {

                    foreach ($GradeID as $key => $items) {
                        $price = $items['price'];
                        Grade::updateAll(['price' => $price],['id' => $key]);
                    }
                    Yii::$app->session->setFlash('', [
                        'type' => 'success',
                        'duration' => 3000,
                        'icon' => 'fa fa-success',
                        'message' => 'The price has been changed successfully',
                        'positonY' => 'bottom',
                        'positonX' => 'center'
                    ]);
                    return $this->render('changeprice', [
                        'model' => $model,
                    ]);
                }else{
                    Yii::$app->session->setFlash('', [
                        'type' => 'warning',
                        'duration' => 3000,
                        'icon' => 'fa fa-warning',
                        'message' => 'Failed to change prices',
                        'positonY' => 'bottom',
                        'positonX' => 'center'
                    ]);
                    return $this->render('changeprice', [
                        'model' => $model,
                    ]);
                }

            } else {
                return $this->render('changeprice', [
                    'model' => $model,
                ]);
            }

        } catch (\Exception $exception) {
            return $exception;
        }


    }


    /**
     * Displays a single Grade model.
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
     * Creates a new Grade model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Grade();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Grade model.
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
     * Deletes an existing Grade model.
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
     * Finds the Grade model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Grade the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grade::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
