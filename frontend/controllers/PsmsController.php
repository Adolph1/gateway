<?php

namespace frontend\controllers;

use frontend\models\Company;
use common\models\User;
use frontend\models\Grade;
use frontend\models\Nozzel;
use frontend\models\OperationRequest;
use backend\models\PumpData;
use backend\models\Sales;
use Da\QrCode\QrCode;
use DateTime;
use frontend\models\CustomerCertificate;
use frontend\models\CustomerConfig;
use frontend\models\DailyCounter;
use frontend\models\Fiscalcountertype;
use frontend\models\Fiscaldaycounters;
use frontend\models\Fiscaldaynumber;
use frontend\models\GlobalCounter;
use frontend\models\Pump;
use frontend\models\ReceiptData;
use frontend\models\SignupForm;
use frontend\models\Taxconfig;
use frontend\models\TraLogs;
use frontend\models\UrlConfig;
use frontend\models\ZReportData;
use frontend\modules\gateway\models\CompanyInvoice;
use frontend\modules\gateway\models\CompanyInvoiceItem;
use kartik\mpdf\Pdf;
use Psr\Log\NullLogger;
use Ripoo\OdooClient;
use SimpleXMLElement;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\ContentNegotiator;
use yii\helpers\Json;
use const http\Client\Curl\Versions\CURL;


class PsmsController extends \yii\rest\ActiveController
{
    public $modelClass = 'frontend\models\TransportFees';

    /* public function behaviors()
     {

         $behaviors = parent::behaviors();
         $behaviors['authenticator'] = [
             'class' => HttpBasicAuth::className(),
             'auth' => function ($username, $password) {

                 $user = User::findByUsername($username);
                 if ($user != null) {
                     return $user->validatePassword($password)
                         ? $user
                         : null;
                 } else {
                     if ($user == null) {
                         $output = Yii::$app->request->get('output');


                         return $output;


                     }
                 }
             }
         ];
         $behaviors['contentNegotiator'] = [
             'class' => ContentNegotiator::className(),
             'formats' => [
                 'application/json' => \yii\web\Response::FORMAT_JSON,
             ],
         ];
         $behaviors['access'] = [
             'class' => AccessControl::className(),
             'only' => ['dashboard', 'login'],
             'rules' => [
                 [
                     'actions' => ['dashboard', 'login'],
                     'allow' => true,
                     'roles' => ['@'],
                 ],
             ],
         ];
         return $behaviors;
     }*/

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    public function actionCommand()
    {

        try {


            if (Yii::$app->request->post()) {
                $data = Yii::$app->request->post('data');
            } else {
                $data = "Ajax failed";
            }

            // return Json
            $username = 'admin';
            $password = 'admin';
            $url = "http://192.168.1.117/jsonPTS";
            $headers = array(
                "Content-Type: application/json; charset=utf-8",
                "Accept: application/json",
                "Authorization: Basic " . base64_encode($username . ":" . $password),
            );
            $datastring = json_encode($data);

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);
            $result = curl_exec($curl);
            curl_close($curl);

            //  $response= json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
            $response = json_decode($result, true);


            return json_encode($response);
        } catch (\Exception $exception) {
            return $exception;
        }
    }

    public function actionProductList()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $products = Grade::find()->select('id,name,price')->all();
        if ($products != null) {


            $data = ['success' => true, 'products' => $products];
            return $data;


        } else {
            $data = ['success' => false, 'data' => 'No Data'];
            return $data;
        }


    }

    public function actionPumpList()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $id = $params['grade_id'];

        $nozzles = Nozzel::find()->select('pump_id')->where(['grade_id' => $id]);
        $pumps = Pump::find()->select('id,name,pump_number')->where(['in', 'id', $nozzles])->all();
        if ($pumps != null) {


            $data = ['pumps' => $pumps];
            return $data;


        } else {
            $data = ['success' => false, 'data' => 'No Data'];
            return $data;
        }


    }

    public function actionPumpNozzles()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $gradeid = $params['grade_id'];
        $pumpid = $params['pump_id'];

        $nozzles = Nozzel::find()->where(['grade_id' => $gradeid, 'pump_id' => $pumpid])->all();

        if ($nozzles != null) {


            $data = ['nozzles' => $nozzles, 'preset_type' => [['name' => Nozzel::AMOUNT], ['name' => Nozzel::VOLUME], ['name' => Nozzel::FULL_TANK]]];
            return $data;


        } else {
            $data = ['success' => false, 'data' => 'No Data'];
            return $data;
        }


    }

    public function actionSale()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $params = Yii::$app->request->post();
        $gradeid = $params['grade_id'];
        $pumpid = $params['pump_id'];
        $nozzleid = $params['nozzle_id'];
        $user = $params['user_id'];
        $type = (int)$params['preset_type'];
        if ($type == Nozzel::VOLUME) {
            $type = 'Volume';
        } elseif ($type == Nozzel::AMOUNT) {
            $type = 'Amount';
        } elseif ($type == Nozzel::FULL_TANK) {
            $type = 'Full Tank';
        }
        $dose = $params['dose'];

        $data = [
            'Protocol' => 'jsonPTS',
            'Packets' => array([
                "Id" => 1,
                "Type" => "PumpAuthorize",
                "Data" => [
                    "Nozzle" => (int)$nozzleid,
                    "Type" => $type,
                    "Dose" => (float)$dose,
                    "Price" => (float)Grade::getPriceById($gradeid)
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
        $datastring = json_encode($data);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);
        $result = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($result, true);


        if ($response) {

            $message = ['Message' => 'OK'];


            return $response;
        } else {
            $response = 'Failed';


            return $response;
        }


    }

    public function actionChangePrice()
    {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {

            if (Yii::$app->request->post()) {

                $grades = Yii::$app->request->post();
                $jsonData = json_decode($grades['details_details'], true);

                $GradeID = $jsonData['product'];

                //   return $GradeID;
                foreach ($GradeID as $key => $items) {

                    $id = (int)$items['product_id'];
                    $productName = $items['product_name'];
                    $price = (float)$items['new_price'];

                    $GradeArray[] = ["Id" => $id,
                        // "ProductID" =>$id ,
                        "Name" => $productName,
                        "Price" => $price,
                        "ExpansionCoefficient" => (float)Grade::geCoefficientById($id),
                    ];
                }
                //return $GradeArray;
                //  print_r($GradeArray);
                // die();
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
            } else {
                $data = "Failed";
            }

            // return Json
            $username = 'admin';
            $password = 'admin';
            $url = "http://192.168.1.117/jsonPTS";
            $headers = array(
                "Content-Type: application/json; charset=utf-8",
                "Accept: application/json",
                "Authorization: Basic " . base64_encode($username . ":" . $password),
            );
            $datastring = json_encode($data);
            // return $data;

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $datastring);
            $result = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($result, true);


            if ($response['Packets'][0]['Message'] == 'OK') {

                // Grade::updateAll(['price' => $price],['id' => $gradeId]);
                $message = ['Message' => 'OK'];


                return $message;
            } else {
                $response = 'Failed';


                return $response;
            }

        } catch (\Exception $exception) {
            return $exception;
        }
    }

    public function actionPts()
    {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $data = file_get_contents('php://input');
        if ($data) {
            //  die;

            $jsonData = new PumpData();
            $jsonData->pump_data = $data;
            $jsonData->status = 0;
            $jsonData->date_time = date('Y-m-d H:i:s');
            $jsonData->qrCode = null;
            // $jsonData->eVFDID = Company::getEvfdId();

            if ($jsonData->save(false)) {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                $JsonToArray = PumpData::find()
                    ->where(['id' => $jsonData->id])
                    ->one();
                $Json = trim($JsonToArray['pump_data']);
                $Json = json_decode($Json);
                $dataID = json_encode($Json->Packets);
                $dataID = trim(str_replace('[', '', $dataID));
                $dataID = trim(str_replace(']', '', $dataID));
                $packet = json_decode($dataID);
                $ID = $packet->Id;
                $PtsId = $Json->PtsId;
                $JsonToArrayID = $JsonToArray['id'];

                //EXTRACTING SALES DATA FROM JSON

                $Data = $packet->Data;

                $trn_dt = $Data->DateTime;
                $Pump = $Data->Pump;
                $Nozzle = $Data->Nozzle;
                $UserId = $Data->UserId;
                $Volume = $Data->Volume;
                $Amount = $Data->Amount;
                $Price = $Data->Price;
                $Transaction = $Data->Transaction;
                $DateTime = $Data->DateTime;
                $currency = 'TZS';
                $sub_total = $Amount;
                $total = $Amount;
                $productId = $Data->FuelGradeId;
                $productName = $Data->FuelGradeName;

                //UPDATE PUMP DATA

                PumpData::updateAll(['transID' => $ID, 'transaction' => $Transaction], ['id' => $jsonData->id]);

                //SAVING SALES DATA
                $sales = new Sales();
                $sales->trn_dt = $trn_dt;
                $sales->date = date('Y-m-d', strtotime($trn_dt));
                $sales->nozzel_no = $Nozzle;
                $sales->pump_no = $Pump;
                $sales->volume = $Volume;
                $sales->price = $Price;
                $sales->sub_total = $sub_total;
                $sales->total = $total;
                $sales->tax = 0.00;
                $sales->currency = $currency;
                $sales->signature = null;
                $sales->pts_transaction_no = $Transaction;
                $sales->print_status = 0;
                $sales->product_id = $productId;
                $sales->product = $productName;
                $success = $sales->save(false);

                $saleID = $sales->id;
                $salePrice = $sales->price;
                $saleVolume = $sales->volume;
                $saleTotal = $sales->total;
                $salePump = $sales->pump_no;
                $saleNozzel = $sales->nozzel_no;
                $saleTrn_dt = $sales->trn_dt;
                $saleProduct = $sales->product;
                $saleTax = $sales->tax;

                if ($success) {

                  //  $this->TRARequest($trn_dt, $total, $saleID, $salePrice, $saleVolume, $saleTotal, $salePump, $saleNozzel, $saleTrn_dt, $saleProduct, $saleTax);

                    $logs = new TraLogs();
                    $logs->status = 'PTS ACK OK';
                    $logs->action = 'ACK TO PTS WITH STATUS OK';
                    $logs->datetime = date('Y-m-d H:i:s');
                    $logs->module = 'PTS';
                    $logs->znumber = date('Ymd');
                    $logs->message = "ACK TO PTS CONTROLLER WITH STATUS OK IS SUCCESSFULLY";
                    $logs->save(false);


                    //Saving data to odoo
                    $this->OdooSales($saleID,$salePrice,$saleVolume,$saleTotal,$salePump,$saleNozzel, $sales->date,$saleProduct,$saleTax);


                    $ResponseData = [
                        'Protocol' => 'jsonPTS',
                        'Packets' => array([
                            "Id" => $ID,
                            "Error" => false,
                            "Message" => "OK"
                        ])
                    ];

                    return $ResponseData;
                } else {

                    $logs = new TraLogs();
                    $logs->status = 'SAVING SALES RECORD';
                    $logs->action = 'SAVING SALES RECORDS FAILED ';
                    $logs->datetime = date('Y-m-d H:i:s');
                    $logs->module = 'PTS CONTROLLER';
                    $logs->znumber = date('Ymd');
                    $logs->message = "PROCESS OF SAVING SALES RECORD FAILED";
                    $logs->save(false);


                    $ResponseData = [
                        'Protocol' => 'jsonPTS',
                        'Packets' => array([
                            "Id" => $ID,
                            "Error" => true,
                            "Message" => "Description of error"
                        ])
                    ];

                    return $ResponseData;
                }


            }
        }
    }

    //TRA TRANSACTION FUNCTION

    public function OdooSales($saleID,$salePrice,$saleVolume,$saleTotal,$salePump,$saleNozzel,$saleTrn_dt,$saleProduct,$saleTax)
    {
        $host = 'http://192.241.129.238:8069';
        $db = 'zrb_erp';
        $user = 'adolph.cm@gmail.com';
        $password = 'password1,';

        $client = new OdooClient($host, $db, $user, $password);
        $line_vals = [
            'trn_date' => $saleTrn_dt,
            'product' => $saleProduct,
            'quantity' => $saleVolume,
            'price'=> $salePrice,
            'amount'=> $saleTotal,
            'station_id' => 2
        ];
//        $order_vals = [
//            'partner_id' => 1,
//            'validity_date' => date('Y-m-d H:i:s'),
//            'order_line' => [[0, 0, $line_vals]],
//        ];

        $id = $client->create('psms.sales', [$line_vals]);


    }



    public function TRARequest($trn_dt, $total, $saleID, $salePrice, $saleVolume, $saleTotal, $salePump, $saleNozzel, $saleTrn_dt, $saleProduct, $saleTax)
    {


        //URL CONFIGURATION FROM BACKEND
        $url = UrlConfig::find()->where(['name' => 'INVOICE/RECEIPT'])->one();
        $domainName = $url['domain_name'];
        $hostURL = $url['url'];

        //for TOKEN REQUEST
        $tokenRequestURL = UrlConfig::find()->where(['name' => 'TOKEN REQUEST'])->one();

        //FOR VERIFY RECEIPT
        $verifyReceiptURL = UrlConfig::find()->where(['name' => 'VERIFY RECEIPT'])->one();
        $verifyReceiptURL = $verifyReceiptURL->url;

        $company = Company::find()->where(['id' => 1])->one();

        $company_id = $company->id;
        $companyName = $company->name;
        $companyAddress = $company->address;
        $username = $company->company_username;
        $password = $company->password;
        $certificate_password = $company->certificate_password;
        $cs = $company->certificate_serial;
        $companyContact_person = $company->contact_person;
        $companyTin = $company->tin;
        $companyVrn = $company->vrn;
        $companySerial_number = $company->serial_number;
        $companyUin = $company->uin;
        $companyTax_office = $company->tax_office;


        $current_time = date('H:i:s', strtotime($trn_dt));
        $verification_time = date('His', strtotime($trn_dt));
        $current_date = date('Y-m-d', strtotime($trn_dt));
        $znum = date('Ymd', strtotime($trn_dt));
        $tin = $company->tin;
        $regId = $company->registration_id;
        $efdSerial = $company->serial_number;
        $RCTVNUM = $company->receipt_number;
        $pmtAmount = $total;

        $custType = 6;
        $custId = null;
        $custName = null;
        $mobile = null;


        $sold_items_data[] =
            ['ITEM' => [
                'ID' => 'UNLEADED-' . $saleID,
                'DESC' => 'UNLEADED ' . $salePrice . ' x ' . $saleVolume,
                'QTY' => $saleVolume,
                'TAXCODE' => 1,
                'AMT' => $saleTotal,
            ]];


        $taxPercent = Taxconfig::find()->where(['company_id' => 1])->one();

        $vatRate = $taxPercent['taxPercent'];


//                    $TaxCoef = ($vatRate * 0.01) + 1;
        $TaxCoef = ($vatRate * 0.01) + 1;

        $amountBeforeTax = $saleTotal / $TaxCoef;
        $totalTaxExcl = round($amountBeforeTax, 2);
        $netAmount = ($totalTaxExcl);
        $taxAmount = round((($saleTotal - $totalTaxExcl)), 2);

        $totalTaxIncl = $saleTotal;


        $DCounter = DailyCounter::find()
            ->where(['company_id' => $company_id])
            ->max('reference_no');

        $check_last = DailyCounter::find()
            ->where(['company_id' => $company_id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($DCounter != '') {

            if ($current_date == $check_last->created_at) {

                $daily_counter = $check_last['reference_no'] + 1;


            } elseif ($current_date > $check_last->created_at) {

                $daily_counter = '1';
            }

        } else {

            $daily_counter = 1;
        }

        $model_global = GlobalCounter::find()
            ->where(['company_id' => $company_id])
            ->max('reference_no');

        $check_last_number = GlobalCounter::find()
            ->where(['company_id' => $company_id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($model_global != null) {

            $global_counter = $check_last_number['reference_no'] + 1;

        } else {

            $global_counter = 1;

        }

        function arrayToXml($array, $rootElement = null, $xml = null)
        {
            $_xml = $xml;
            // If there is no Root Element then insert root
            if ($_xml === null) {
                $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<RCT/>');
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


        $discount = 0.00;
        $pmtType = 'CASH';
        $array_item_data = $sold_items_data;
        $vatTotal = array('VATRATE' => 'A',
            'NETTAMOUNT' => $netAmount,
            'TAXAMOUNT' => $taxAmount
        );
        $total = array(//'TOTALTAXEXCL' => $totalTaxExcl,
            'TOTALTAXEXCL' => $totalTaxIncl,
            'TOTALTAXINCL' => $totalTaxIncl,
            'DISCOUNT' => $discount
        );
        $payment = array('PMTTYPE' => $pmtType,
            'PMTAMOUNT' => $pmtAmount
        );

        $fiscal_code = $RCTVNUM . $global_counter;
        $RCTNUM = $global_counter;

        $arrayReceipt = array('DATE' => $current_date,
            'TIME' => $current_time,
            'TIN' => $tin,
            'REGID' => $regId,
            'EFDSERIAL' => $efdSerial,
            'CUSTIDTYPE' => $custType,
            'CUSTID' => $custId,
            'CUSTNAME' => $custName,
            'MOBILENUM' => $mobile,
            'RCTNUM' => $RCTNUM,
            'DC' => $daily_counter,
            'GC' => $global_counter,
            'ZNUM' => $znum,
            'RCTVNUM' => $fiscal_code,
            'ITEMS' => $array_item_data,
            'TOTALS' => $total,
            'PAYMENTS' => $payment,
            'VATTOTALS' => $vatTotal
        );

        $xml = arrayToXml($arrayReceipt);
        $xml_converted = trim(str_replace('<?xml version="1.0"?>', '', $xml));

        $item_key_data = array_keys($array_item_data);
        $itemsArraySize = count($array_item_data);

        for ($i = 0; $i < $itemsArraySize; $i++) {
            $string = $item_key_data[$i] . ',';
            $string_serialize = rtrim($string, ", ");
            $xml_converted = trim(str_replace("<$string_serialize>", '', $xml_converted));
            $xml_converted = trim(str_replace("</$string_serialize>", '', $xml_converted));
        }

        $xml_converted = str_replace("\r\n", "", $xml_converted);

        $xml_converted = simplexml_load_string($xml_converted);
        $xml_converted = dom_import_simplexml($xml_converted)->ownerDocument;
        $xml_converted = $xml_converted->saveXML($xml_converted->documentElement, LIBXML_NOEMPTYTAG);


        //CHECKING SERVER IS AVAILABLE OR NOT
        if (checkdnsrr($domainName, 'ANY')) {

            $ref = $salePump . $saleNozzel . $company->tin . $global_counter;

            //VERIFY URL IN ORDER TO GET TOKEN
            $urlToken = $tokenRequestURL['url'];

            $headerToken = array(
                'Content-type: application/x-www-form-urlencoded',
            );
            // $userData = "username=babaadjc8490hedy&password=TdfL6o$3pzndV9v[&grant_type=password";
            $userData = "username=$username&password=$password&grant_type=password";
            $curlToken = curl_init($urlToken);
            curl_setopt($curlToken, CURLOPT_HTTPHEADER, $headerToken);
            curl_setopt($curlToken, CURLOPT_POST, true);
            curl_setopt($curlToken, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlToken, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curlToken, CURLOPT_POSTFIELDS, $userData);
            curl_setopt($curlToken, CURLOPT_RETURNTRANSFER, true);
            try {
                $resultToken = curl_exec($curlToken);
                if (curl_errno($curlToken)) {
                    throw new \Exception(curl_error($curlToken));
                }
                curl_close($curlToken);

                $at = json_decode($resultToken, true);
                $access_token = $at['access_token'];

            } catch (\Exception $e) {
                //CALLING OFFLINE BLOCK

                $this->offlineBlock($xml_converted, $company_id, $fiscal_code, $saleID, $saleTrn_dt, $znum,
                    $netAmount, $totalTaxIncl, $discount, $pmtType, $taxAmount, $vatRate, $daily_counter,
                    $global_counter, $verifyReceiptURL, $verification_time, $companyName, $companyAddress,
                    $companyContact_person, $companyTin, $companyVrn, $companySerial_number, $companyUin, $companyTax_office,
                    $RCTNUM, $current_date, $current_time, $salePump, $saleNozzel, $saleProduct, $saleVolume, $salePrice, $saleTotal, $saleTax);

            }

            $cert_info = array();
            $cert_store = file_get_contents("file/$company->file");
            $isGenerated = openssl_pkcs12_read($cert_store, $cert_info, $certificate_password);
            if (!$isGenerated) {
                throw new \RuntimeException(('Invalid Password'));
            }
            $public_key = $cert_info['cert'];
            $private_key = $cert_info['pkey'];
            $pkeyid = openssl_get_privatekey($private_key);
            if (!$pkeyid) {
                throw new \RuntimeException(('Invalid private key'));
            }
            $xml_doc = "<?xml version='1.0' encoding='UTF-8'?>";
            $efdms_open = "<EFDMS>";
            $efdms_close = "</EFDMS>";


            $isGenerated2 = openssl_sign($xml_converted, $signature, $pkeyid, OPENSSL_ALGO_SHA1);
            openssl_free_key($pkeyid);
            if (!$isGenerated2) {
                throw new \RuntimeException(('Computing of the signature failed'));
            }
            $signature_value = base64_encode($signature);
            $efdmsSignature = "<EFDMSSIGNATURE>$signature_value</EFDMSSIGNATURE>";
            $signedData = $xml_doc . $efdms_open . $xml_converted . $efdmsSignature . $efdms_close;

            $urlReceipt = $hostURL;

            $routing_key = 'vfdrct';
            $headers = array(
                'Content-type: Application/xml',
                'Routing-Key: ' . $routing_key,
                'Cert-Serial: ' . base64_encode($cs),
                'Authorization: Bearer ' . $access_token,
            );
            $curl = curl_init($urlReceipt);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $signedData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            try {
                $resultEfd = curl_exec($curl);
                if (curl_errno($curl)) {
                    throw new \Exception(curl_error($curl));
                }
                curl_close($curl);

                $data = new SimpleXMLElement($resultEfd);
                $responseStatus = (string)$data->RCTACK[0]->ACKMSG;

                if ($responseStatus == "Success") {

                    $logs = new TraLogs();
                    $logs->status = 'SUCCESS';
                    $logs->action = 'SENDING RECEIPT DATA';
                    $logs->datetime = date('Y-m-d H:i:s');
                    $logs->module = 'ONLINE';
                    $logs->znumber = date('Ymd');
                    $logs->message = "SENDING RECEIPT DATA SUCCESS";
                    $logs->save(false);

                    Sales::updateAll(['qr_code' => $fiscal_code, 'status' => 3], ['id' => $saleID]);


                    $zreport = new ZReportData();
                    $zreport->datetime = $saleTrn_dt;
                    $zreport->znumber = $znum;
                    $zreport->nettamount = $netAmount;
                    $zreport->company_id = $company_id;
                    $zreport->pmtamount = $totalTaxIncl;
                    $zreport->fiscal_code = $fiscal_code;
                    $zreport->discount = $discount;
                    $zreport->pmttype = $pmtType;
                    $zreport->taxamount = $taxAmount;
                    $zreport->vatrate = $vatRate;
                    $zreport->status = ZReportData::CREATED_DATA;
                    $zreport->save(false);


                    $daily = new DailyCounter();
                    $daily->reference_no = $daily_counter;
                    $daily->company_id = $company->id;
                    $daily->created_at = date('Y-m-d');
                    $daily->created_by = '';
                    $daily->save(false);

                    $global = new GlobalCounter();
                    $global->reference_no = $global_counter;
                    $global->company_id = $company->id;
                    $global->created_at = date('Y-m-d');
                    $global->created_by = '';
                    $global->save(false);


                    $receiptData = new ReceiptData();
                    $receiptData->receipt_data = $signedData;
                    $receiptData->company_id = $company_id;
                    $receiptData->fiscal_code = $fiscal_code;
                    $receiptData->transaction_id = $saleID;
                    $receiptData->status = ReceiptData::DATA_SENT;
                    $receiptData->response_status = $responseStatus;
                    $receiptData->response_ack = $resultEfd;
                    $receiptData->created_at = date('Y-m-d H:i:s');
                    $receiptData->save(false);
                    $qrCode = (new QrCode($verifyReceiptURL . $fiscal_code . '_' . $verification_time));

                    $pdf = new Pdf([
                        'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
                        'format' => [110, 180],
                        'marginTop' => 5,
                        'marginBottom' => 5,
                        'marginLeft' => 5,
                        'marginRight' => 5,

                    ]);
                    $mpdf = $pdf->api;
                    $mpdf->WriteHtml('
                   <style>
                     #invoice-data{
                     font-family: Tahoma;
                     }
                   #legal h4{
                    font-size:0.7em;
                    font-weight: 300;
                    alignment: center;
                        padding-left: 39px;
                   }
                   #pic{
                   alignment: center;
                   padding-left: 79px;
                   margin-top: -15px
                   }
                   #trader{
                   text-align: center;
                   alignment: center;
                   font-size:0.7em;
                    font-weight: 300;
                     padding-right: 150px;
                      margin-top: -27px
                   } 
                    
                    #office{
                   text-align: center;
                   alignment: center;
                   font-size:0.7em;
                    font-weight: 300;
                     padding-right: 150px;
                      margin-top: -24px
                   }
                     #uin{
                    font-size:0.7em;
            padding-right: -39px;
          
                   }           #pump{
                    font-size:0.7em;
            padding-right: -39px;
            padding-top:-10px;
                   }  
                              #items{
                    font-size:0.7em;
            padding-right: -39px;
                   }   
                                    #item{
                    font-size:0.7em;
                    margin: 15px;
                   }
                    </style>


       <div id="invoice-data">
            <div id="legal">
            <h4> *** START OF LEGAL RECEIPT *** </h4>
            </div>
            <div id="pic">
             <img src="frontend/web/logo/tra_logo.png" width="65px" height="65px">
            </div>
            <div id="trader">
            <p>
               <div > ' . $companyName . '</br></div>
               <div> ' . $companyAddress . '</br></div>
               <div> MOBILE: ' . $companyContact_person . '.</br></div>
              <div>  TIN: ' . $companyTin . '</br></div>
              <div>  VRN: ' . $companyVrn . '</br></div>
               <div>  SERIAL NO: ' . $companySerial_number . '</br></div>
            </p>
            </div>
            <div id="uin">
              <div>  UIN:' . $companyUin . '</br></div>
              
            </div>
              <div id="office">
            <p>
            <div>  TAX OFFICE: ' . $companyTax_office . '</br></div>
            </p>
            </div>
     
        
            <div id="uin">
              <table>
                <tr id="items">
                    <td id="item">
                            RECEIPT NO: ' . $RCTNUM . '
                    </td>
                    <td id="item" style="padding-left: 60px">
                         Z NO:  ' . $RCTNUM . '/' . $znum . '
                    </td>
                </tr>
              </table>      
            </div>   
                 
                 
           <div id="uin">
              <table>
                <tr id="items">
                    <td id="item">
                            RECEIPT DATE: ' . $current_date . '
                    </td>
                    <td id="item" style="padding-left: 30px">
                          TIME :  ' . $current_time . '
                    </td>
                </tr>
              </table>      
            </div>
           
          --------------------------------------------------
            
                 <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                            PUMP: ' . $salePump . '
                    </td>
                    <td id="item" style="padding-left: 60px">
                         NOZZLE :  ' . $saleNozzel . '
                    </td>
                </tr>   
                 <tr id="items">
                    <td id="item">
                           ' . $saleProduct . ': ' . $saleVolume . ' x ' . $salePrice . '
                    </td>
                    <td id="item" style="padding-left: 60px">
                          ' . Yii::$app->formatter->asDecimal($saleTotal, 2) . '
                    </td>
                </tr>
              </table>      
            </div>
         --------------------------------------------------
            
                <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                           <strong>TOTAL EXCL TAX:</strong> 
                    </td>
                    <td id="item" style="padding-left: 50px">
                     <strong>' . Yii::$app->formatter->asDecimal($saleTotal, 2) . '</strong> 
                    </td>
                </tr>
              </table>      
            </div>
            
             --------------------------------------------------
             
                          <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                           <strong>TOTAL TAX:</strong> 
                    </td>
                    <td id="item" style="padding-left: 105px">
                     <strong>' . Yii::$app->formatter->asDecimal($saleTax, 2) . '</strong> 
                    </td>
                </tr>
              </table>      
            </div>
            
                --------------------------------------------------
             
                          <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                           <strong>TOTAL INCL TAX:</strong> 
                    </td>
                    <td id="item" style="padding-left: 50px">
                     <strong>' . Yii::$app->formatter->asDecimal($saleTotal, 2) . '</strong> 
                    </td>
                </tr>
              </table>      
            </div>
            --------------------------------------------------
             <div id="trader">
             <p>
              <div>  RECEIPT VERIFICATION CODE</div></br>
              <strong>   ' . $fiscal_code . '</strong></br>
              <div> <img src="' . $qrCode->writeDataUri() . '" width="75px" height="75px"><br/></div>
               </p>
             </div>
              --------------------------------------------------
               <div id="legal">
            <h4> *** END OF LEGAL RECEIPT *** </h4>
            </div>
            
       </div>');
                    $filename = date('YmdHsi') . $saleID . '.pdf';
                    $mpdf->Output('uploads/' . $filename, 'F');;
                    $filename1 = 'uploads/' . $filename;
                    // print_r($filename1);
                    //  die();
                    exec("lp $filename1");

                    return '';

                }

            } catch (\Exception $e) {

                // CALLING OFFLINE BLOCK

                $this->offlineBlock($xml_converted, $company_id, $fiscal_code, $saleID, $saleTrn_dt, $znum,
                    $netAmount, $totalTaxIncl, $discount, $pmtType, $taxAmount, $vatRate, $daily_counter,
                    $global_counter, $verifyReceiptURL, $verification_time, $companyName, $companyAddress,
                    $companyContact_person, $companyTin, $companyVrn, $companySerial_number, $companyUin, $companyTax_office,
                    $RCTNUM, $current_date, $current_time, $salePump, $saleNozzel, $saleProduct, $saleVolume, $salePrice, $saleTotal, $saleTax);
            }


        } else {
            //OFFLINE BLOCK

            $this->offlineBlock($xml_converted, $company_id, $fiscal_code, $saleID, $saleTrn_dt, $znum,
                $netAmount, $totalTaxIncl, $discount, $pmtType, $taxAmount, $vatRate, $daily_counter,
                $global_counter, $verifyReceiptURL, $verification_time, $companyName, $companyAddress,
                $companyContact_person, $companyTin, $companyVrn, $companySerial_number, $companyUin, $companyTax_office,
                $RCTNUM, $current_date, $current_time, $salePump, $saleNozzel, $saleProduct, $saleVolume, $salePrice, $saleTotal, $saleTax);

        }


    }


    public function actionLogin1()
    {
        $response = [

            'success' => true,
            'access_token' => Yii::$app->user->identity->getAuthKey(),
            'username' => Yii::$app->user->identity->username,
            'user_id' => Yii::$app->user->identity->user_id,
            'status' => Yii::$app->user->identity->status,


        ];
        return $response;

    }

    public function actionLogin()
    {
        Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

        $model = new \common\models\LoginForm();
        $params = Yii::$app->request->post();
        $model->username = $params['username'];
        $model->password = $params['password'];


        $user = User::findByUsername($model->username);
        if (!empty($user)) {
            if ($model->login()) {
                $token = \Yii::$app->security->generateRandomString();
                User::updateAll(['auth_key' => $token], ['username' => $model->username]);

                $response = [
                    'operational_status' => 1,
                    "user" => [
                        'access_token' => Yii::$app->user->identity->getAuthKey(),
                        'username' => Yii::$app->user->identity->username,
                        'user_id' => Yii::$app->user->identity->user_id,
                        'role' => Yii::$app->user->identity->role,
                        'status' => Yii::$app->user->identity->status,
                    ]
                ];

                return $response;

            } else {
                $response['error'] = false;
                $response['operational_status'] = 0;
                $response['message'] = 'wrong password or username';
                return $response;
            }
        } else {
            $response['error'] = false;
            $response['operational_status'] = 0;
            $response['message'] = 'user is disabled or does not exist!';
            return $response;
        }

    }


    //OFFLINE BLOCK
    private function offlineBlock($xml_converted, $company_id, $fiscal_code, $saleID, $saleTrn_dt, $znum,
                                  $netAmount, $totalTaxIncl, $discount, $pmtType, $taxAmount, $vatRate, $daily_counter,
                                  $global_counter, $verifyReceiptURL, $verification_time, $companyName, $companyAddress,
                                  $companyContact_person, $companyTin, $companyVrn, $companySerial_number, $companyUin, $companyTax_office,
                                  $RCTNUM, $current_date, $current_time, $salePump, $saleNozzel, $saleProduct, $saleVolume, $salePrice, $saleTotal, $saleTax)
    {
        $receiptData = new ReceiptData();
        $receiptData->receipt_data = $xml_converted;
        $receiptData->company_id = $company_id;
        $receiptData->fiscal_code = $fiscal_code;
        $receiptData->transaction_id = $saleID;
        $receiptData->status = ReceiptData::DATA_NOT_SENT;
        $receiptData->response_status = '';
        $receiptData->response_ack = '';
        $receiptData->created_at = date('Y-m-d H:i:s');
        $receiptData->save(false);


        $logs = new TraLogs();
        $logs->status = 'OFFLINE';
        $logs->action = 'SAVING RECEIPT DATA OFFLINE';
        $logs->datetime = date('Y-m-d H:i:s');
        $logs->module = 'OFFLINE';
        $logs->znumber = date('Ymd');
        $logs->message = "SAVING OFFLINE RECEIPT DATA SUCCESS";
        $logs->save(false);


        $zreport = new ZReportData();
        $zreport->datetime = $saleTrn_dt;
        $zreport->znumber = $znum;
        $zreport->nettamount = $netAmount;
        $zreport->company_id = $company_id;
        $zreport->pmtamount = $totalTaxIncl;
        $zreport->fiscal_code = $fiscal_code;
        $zreport->discount = $discount;
        $zreport->pmttype = $pmtType;
        $zreport->taxamount = $taxAmount;
        $zreport->vatrate = $vatRate;
        $zreport->status = ZReportData::CREATED_DATA;
        $zreport->save(false);


        $daily = new DailyCounter();
        $daily->reference_no = $daily_counter;
        $daily->company_id = $company_id;
        $daily->created_at = date('Y-m-d');
        $daily->created_by = '';
        $daily->save(false);

        $global = new GlobalCounter();
        $global->reference_no = $global_counter;
        $global->company_id = $company_id;
        $global->created_at = date('Y-m-d');
        $global->created_by = '';
        $global->save(false);

        $qrCode = (new QrCode($verifyReceiptURL . $fiscal_code . '_' . $verification_time))
            ->setSize(70)
            ->setMargin(5);
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'format' => [110, 180],
            'marginTop' => 5,
            'marginBottom' => 5,
            'marginLeft' => 5,
            'marginRight' => 5,

        ]);
        $mpdf = $pdf->api;
        $mpdf->WriteHtml('
                         <style>
                     #invoice-data{
                     font-family: Tahoma;
                     }
                   #legal h4{
                    font-size:0.7em;
                    font-weight: 300;
                    alignment: center;
                        padding-left: 39px;
                   }
                   #pic{
                   alignment: center;
                   padding-left: 79px;
                   margin-top: -15px
                   }
                   #trader{
                   text-align: center;
                   alignment: center;
                   font-size:0.7em;
                    font-weight: 300;
                     padding-right: 150px;
                      margin-top: -27px
                   } 
                    
                    #office{
                   text-align: center;
                   alignment: center;
                   font-size:0.7em;
                    font-weight: 300;
                     padding-right: 150px;
                      margin-top: -24px
                   }
                     #uin{
                    font-size:0.7em;
            padding-right: -39px;
          
                   }           #pump{
                    font-size:0.7em;
            padding-right: -39px;
            padding-top:-10px;
                   }  
                              #items{
                    font-size:0.7em;
            padding-right: -39px;
                   }   
                                    #item{
                    font-size:0.7em;
                    margin: 15px;
                   }
                    </style>


       <div id="invoice-data">
            <div id="legal">
            <h4> *** START OF LEGAL RECEIPT *** </h4>
            </div>
            <div id="pic">
             <img src="frontend/web/logo/tra_logo.png" width="65px" height="65px">
            </div>
            <div id="trader">
         <p>
               <div > ' . $companyName . '</br></div>
               <div> ' . $companyAddress . '</br></div>
               <div> MOBILE: ' . $companyContact_person . '.</br></div>
              <div>  TIN: ' . $companyTin . '</br></div>
              <div>  VRN: ' . $companyVrn . '</br></div>
               <div>  SERIAL NO: ' . $companySerial_number . '</br></div>
            </p>
            </div>
            <div id="uin">
              <div>  UIN:' . $companyUin . '</br></div>
              
            </div>
              <div id="office">
            <p>
            <div>  TAX OFFICE: ' . $companyTax_office . '</br></div>
            </p>
            </div>
     
        
            <div id="uin">
              <table>
                <tr id="items">
                    <td id="item">
                            RECEIPT NO: ' . $RCTNUM . '
                    </td>
                    <td id="item" style="padding-left: 60px">
                         Z NO:  ' . $RCTNUM . '/' . $znum . '
                    </td>
                </tr>
              </table>      
            </div>   
                 
                 
           <div id="uin">
              <table>
                <tr id="items">
                    <td id="item">
                            RECEIPT DATE: ' . $current_date . '
                    </td>
                    <td id="item" style="padding-left: 30px">
                          TIME :  ' . $current_time . '
                    </td>
                </tr>
              </table>      
            </div>
           
          --------------------------------------------------
            
                 <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                            PUMP: ' . $salePump . '
                    </td>
                    <td id="item" style="padding-left: 60px">
                         NOZZLE :  ' . $saleNozzel . '
                    </td>
                </tr>   
                 <tr id="items">
                    <td id="item">
                           ' . $saleProduct . ': ' . $saleVolume . ' x ' . $salePrice . '
                    </td>
                    <td id="item" style="padding-left: 60px">
                          ' . Yii::$app->formatter->asDecimal($saleTotal, 2) . '
                    </td>
                </tr>
              </table>      
            </div>
         --------------------------------------------------
            
                <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                           <strong>TOTAL EXCL TAX:</strong> 
                    </td>
                    <td id="item" style="padding-left: 50px">
                     <strong>' . Yii::$app->formatter->asDecimal($saleTotal, 2) . '</strong> 
                    </td>
                </tr>
              </table>      
            </div>
            
             --------------------------------------------------
             
                          <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                           <strong>TOTAL TAX:</strong> 
                    </td>
                    <td id="item" style="padding-left: 105px">
                     <strong>' . Yii::$app->formatter->asDecimal($saleTax, 2) . '</strong> 
                    </td>
                </tr>
              </table>      
            </div>
            
                --------------------------------------------------
            
                          <div id="pump">
              <table>
                <tr id="items">
                    <td id="item">
                           <strong>TOTAL INCL TAX:</strong> 
                    </td>
                    <td id="item" style="padding-left: 50px">
                     <strong>' . Yii::$app->formatter->asDecimal($saleTotal, 2) . '</strong> 
                    </td>
                </tr>
              </table>      
            </div>
            --------------------------------------------------
             <div id="trader">
             <p>
              <div>  RECEIPT VERIFICATION CODE</div></br>
              <strong>   ' . $fiscal_code . '</strong></br>
              <div> <img src="' . $qrCode->writeDataUri() . '" width="75px" height="75px"><br/></div>
               </p>
             </div>
              --------------------------------------------------
               <div id="legal">
            <h4> *** END OF LEGAL RECEIPT *** </h4>
            </div>
            
       </div>
                          ');
        $filename = date('YmdHsi') . $saleID . '.pdf';
        $mpdf->Output('uploads/' . $filename, 'F');;
        $filename1 = 'uploads/' . $filename;
        // print_r($filename1);
        //  die();
        exec("lp $filename1");

        return '';
    }

    protected function verbs()
    {
        return [
            'login' => ['POST'],
            'pts' => ['POST'],
            'change-price' => ['POST'],

        ];
    }


}



