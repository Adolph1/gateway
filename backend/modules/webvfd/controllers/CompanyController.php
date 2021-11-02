<?php

namespace backend\modules\webvfd\controllers;

use backend\modules\webvfd\models\UrlConfig;
use backend\modules\webvfd\models\ValidLicence;
use backend\modules\webvfd\models\WebVfdApi;
use Yii;
use backend\modules\webvfd\models\Company;
use backend\modules\webvfd\models\CompanySearch;
use yii\bootstrap\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use SimpleXMLElement;

/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyController extends Controller
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
     * Lists all Company models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Company model.
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
     * Creates a new Company model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new Company(['scenario' => 'create']);
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);

        }

        $model->created_at = date('Y-m-d H:i:s');
        $model->create_by = Yii::$app->user->identity->username;
        $model->reg_status = Company::NOT_REGISTERED;

        if ($model->load(Yii::$app->request->post())) {
            $model->pfx_file = UploadedFile::getInstance($model, 'pfx_file');

            if ($model->pfx_file) {

                $model->pfx_file = UploadedFile::getInstance($model, 'pfx_file');
                $model->pfx_file->saveAs('uploads/file/' . $model->tin . date('YmdHi') . '.' . $model->pfx_file->extension);
                $model->file = $model->tin . date('YmdHi') . '.' . $model->pfx_file->extension;

            }


            ############################ IF SAVE COMPANY IS SUCCESSFULLY ACTIVATE LICENCE #############################
            if ($model->save(false)) {

                $active_licence = new ValidLicence();
                $active_licence->company_id = $model->id;
                $active_licence->activation_date = date('Y-m-d');
                $active_licence->expired_date = date('Y-m-d', strtotime('+1 year'));
                $active_licence->created_at = date('Y-m-d H:i:s');
                $active_licence->created_by = Yii::$app->user->identity->getId();
                $active_licence->save();

            }

            Yii::$app->session->setFlash('', [
                'type' => 'success',
                'duration' => 7000,
                'icon' => 'fa fa-warning',
                'title' => 'Notification',
                'message' => 'Company was created successfully and licence activated up to date ' . $active_licence->expired_date,
                'positonY' => 'down',
                'positonX' => 'right'
            ]);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            $model->pfx_file = UploadedFile::getInstance($model, 'pfx_file');

            if ($model->pfx_file) {
                $model->pfx_file = UploadedFile::getInstance($model, 'pfx_file');
                $model->pfx_file->saveAs('uploads/file/' . $model->tin . date('YmdHi') . '.' . $model->pfx_file->extension);
                $model->file = $model->tin . date('YmdHi') . '.' . $model->pfx_file->extension;
            }

            $model->save(false);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionRegister($id)
    {


        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = $this->findModel($id);

        $is_active = Company::find()->where(['id' => $id])->andWhere(['status' => Company::ACTIVE])->one();

        $url = UrlConfig::find()->where(['name'=>'REGISTRATION'])->one();
        $domainName = $url['domain_name'];
        $hostURL = $url['url'];

        //print_r($domainName);
       // die();

        if (!empty($is_active)) {

            $companySerialNumber = $model->certificate_serial;
            $TIN = $model->tin;
            $SERIAL = $model->serial_number;
            $certificate_password = $model->certificate_password;

            try {

                $cert_store = file_get_contents("uploads/file/$model->file");

            } catch (\Exception $e) {

                Yii::$app->session->setFlash('', [
                    'type' => 'danger',
                    'duration' => 7000,
                    'icon' => 'fa fa-warning',
                    'title' => 'Notification',
                    'message' => 'Pfx document is invalid, Please upload and try again',
                    'positonY' => 'bottom',
                    'positonX' => 'right'
                ]);

                return $this->redirect(['view', 'id' => $model->id]);

            }

            $encodedSerial = base64_encode($companySerialNumber);
            $headers = array(
                'Content-type: Application/xml',
                'Cert-Serial: ' . $encodedSerial,
                'Client: webapi ',
            );

            $arrayReceipt = array(
                'TIN' => $TIN,
                'SERIAL' => $SERIAL,
            );
//            print_r($headers);
//            die();

            function arrayToXml($array, $rootElement = null, $xml = null)
            {
                $_xml = $xml;
                // If there is no Root Element then insert root
                if ($_xml === null) {
                    $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<REGDATA/>');
                }

                // Visit all key value pair
                foreach ($array as $k => $v) {

                    // If there is nested array then
                    if (is_array($v)) {

                        // Call function for nested array
                        arrayToXml($v, $k, $_xml->addChild($k));

                    } else {

                        // Simply add child element.
                        $_xml->addChild($k, $v);
                    }
                }

                return $_xml->asXML();
            }

            $xml = arrayToXml($arrayReceipt);

            $xml_converted = trim(str_replace('<?xml version="1.0"?>', '', $xml));


            $cert_info = array();

            $isGenerated = openssl_pkcs12_read($cert_store, $cert_info, $certificate_password);

            if (!$isGenerated) {

                //  throw new \RuntimeException(('Invalid Password'));

                Yii::$app->session->setFlash('', [
                    'type' => 'danger',
                    'duration' => 7000,
                    'icon' => 'fa fa-warning',
                    'title' => 'Notification',
                    'message' => 'Invalid password',
                    'positonY' => 'bottom',
                    'positonX' => 'right'
                ]);
                return $this->redirect(['view', 'id' => $model->id]);
            }

            $public_key = $cert_info['cert'];

            $private_key = $cert_info['pkey'];

            $pkeyid = openssl_get_privatekey($private_key);

            //  return $pkeyid;
            if (!$pkeyid) {
                // throw new \RuntimeException(('Invalid private key'));

                Yii::$app->session->setFlash('', [
                    'type' => 'danger',
                    'duration' => 7000,
                    'icon' => 'fa fa-warning',
                    'title' => 'Notification',
                    'message' => 'Invalid private key',
                    'positonY' => 'bottom',
                    'positonX' => 'right'
                ]);
                return $this->redirect(['view', 'id' => $model->id]);
            }

            $isGenerated2 = openssl_sign($xml_converted, $signature, $pkeyid, OPENSSL_ALGO_SHA1);

            openssl_free_key($pkeyid);
            if (!$isGenerated2) {
                //   throw new \RuntimeException(('Computing of the signature failed'));

                Yii::$app->session->setFlash('', [
                    'type' => 'danger',
                    'duration' => 7000,
                    'icon' => 'fa fa-warning',
                    'title' => 'Notification',
                    'message' => 'Computing of the signature failed',
                    'positonY' => 'bottom',
                    'positonX' => 'right'
                ]);
                return $this->redirect(['view', 'id' => $model->id]);
            }
            $signatureGenerated = base64_encode($signature);


            $data = "<?xml version='1.0' encoding='UTF-8'?>
                    <EFDMS>
                    <REGDATA>
                      <TIN>$TIN</TIN>
                      <SERIAL>$SERIAL</SERIAL>
                    </REGDATA>
                    <EFDMSSIGNATURE>$signatureGenerated</EFDMSSIGNATURE>
                    </EFDMS>";


            $curl = curl_init($hostURL);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $resultEfd = curl_exec($curl);
            if (curl_errno($curl)) {
                throw new \Exception(curl_error($curl));
            }

            curl_close($curl);
            //    return $resultEfd;

            // $efdms_response = XmlTo::convert($resultEfd, $outputRoot = false);
            $data = new SimpleXMLElement($resultEfd);

            $companyName = (string)$data->EFDMSRESP[0]->NAME;
            $tin = (string)$data->EFDMSRESP[0]->TIN;
            $vrn = (string)$data->EFDMSRESP[0]->VRN;
            $uin = (string)$data->EFDMSRESP[0]->UIN;
            $receiptCode = (string)$data->EFDMSRESP[0]->RECEIPTCODE;
            $registrationID = (string)$data->EFDMSRESP[0]->REGID;
            $taxOffice = (string)$data->EFDMSRESP[0]->TAXOFFICE;
            $address = (string)$data->EFDMSRESP[0]->ADDRESS;
            $street = (string)$data->EFDMSRESP[0]->STREET;
            $city = (string)$data->EFDMSRESP[0]->CITY;
            $country = (string)$data->EFDMSRESP[0]->COUNTRY;
            $contactPerson = (string)$data->EFDMSRESP[0]->MOBILE;
            $username = (string)$data->EFDMSRESP[0]->USERNAME;
            $password = (string)$data->EFDMSRESP[0]->PASSWORD;
            $response_status = (string)$data->EFDMSRESP[0]->ACKMSG;

            //  return $data;


            //FOR CHECK IF COMPANY HAS ALREADY REGISTERED TO TRA PORTAL
            $is_registeredToTRA = Company::find()
                ->where(['id' => $id])
                ->andWhere(['reg_status' => Company::ALREADY_REGISTERED])
                ->andWhere(['status' => Company::ACTIVE])
                ->one();

            if(!empty($is_registeredToTRA)){

                Yii::$app->session->setFlash('', [
                    'type' => 'danger',
                    'duration' => 7000,
                    'icon' => 'fa fa-danger',
                    'title' => 'Notification',
                    'message' => 'This company '. $model->name .' has already been registered to TRA portal',
                    'positonY' => 'bottom',
                    'positonX' => 'right'
                ]);
                return $this->redirect(['view', 'id' => $model->id]);

            }else{

                if($response_status == "Registration Successful"){

                    Company::updateAll([
                        'name'=>$companyName,
                        'vrn'=>$vrn,
                        'receipt_number'=>$receiptCode,
                        'registration_id'=>$registrationID,
                        'uin'=>$uin,
                        'tax_office'=>$taxOffice,
                        'address'=>$address,
                        'street'=>$street,
                        'city'=>$city,
                        'country'=>$country,
                        'contact_person'=>$contactPerson,
                        'company_username'=>$username,
                        'password'=>$password,
                        'reg_status'=>Company::ALREADY_REGISTERED,
                        'updated_at'=>date('Y-m-d H:m:s'),
                    ],
                        ['id'=>$id]);

                    Yii::$app->session->setFlash('', [
                        'type' => 'success',
                        'duration' => 7000,
                        'icon' => 'fa fa-check',
                        'title' => 'Notification',
                        'message' => 'This company '. $model->name .' is registered successfully  to TRA',
                        'positonY' => 'bottom',
                        'positonX' => 'right'
                    ]);
                    return $this->redirect(['view', 'id' => $model->id]);

                }else{

                    Yii::$app->session->setFlash('', [
                        'type' => 'danger',
                        'duration' => 7000,
                        'icon' => 'fa fa-warning',
                        'title' => 'Notification',
                        'message' => 'This company '. $model->name .' is  not successfully registered to TRA system, so please try again later',
                        'positonY' => 'bottom',
                        'positonX' => 'right'
                    ]);
                    return $this->redirect(['view', 'id' => $model->id]);

                }
            }

        } else {

            Yii::$app->session->setFlash('', [
                'type' => 'danger',
                'duration' => 7000,
                'icon' => 'fa fa-danger',
                'title' => 'Notification',
                'message' => 'This company '. $model->name .' is not active, please communicate with system administrator',
                'positonY' => 'bottom',
                'positonX' => 'right'
            ]);
            return $this->redirect(['view', 'id' => $model->id]);
        }


    }

    /**
     * Deletes an existing Company model.
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
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }


    public function actionGetToken($id)
    {
        $companyDetails = Company::getCompanyDetailsById($id);
        $url = UrlConfig::getUrlByName(WebVfdApi::TOKEN_ACCESS);

        $token = WebVfdApi::getApiToken($companyDetails->company_username, $companyDetails->password,$url->url);
        if($token != null){
            print_r($token);
        }

    }
}
