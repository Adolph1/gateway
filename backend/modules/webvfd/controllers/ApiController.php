<?php


namespace backend\modules\webvfd\controllers;


use backend\modules\webvfd\models\IncomingSalesData;
use frontend\models\PumpData;
use backend\modules\webvfd\models\Company;
use backend\modules\webvfd\models\UrlConfig;
use backend\modules\webvfd\models\WebVfdApi;
use common\models\LoginForm;
use common\models\User;
use Da\QrCode\QrCode;
use frontend\models\DailyCounter;
use frontend\models\GlobalCounter;
use frontend\models\ReceiptData;
use frontend\models\Sales;
use frontend\models\Taxconfig;
use frontend\models\TraLogs;
use frontend\models\ZReportData;
use kartik\mpdf\Pdf;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use SimpleXMLElement;



class ApiController extends Controller
{
    public function behaviors()
    {

        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'auth' => function ($username, $password) {
                $user = User::findByUsername($username);
                return $user->validatePassword($password)
                    ? $user
                    : null;
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


    }

    public function actionLogin()
    {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
        $response = [

            'success'=>true,
            'access_token' => Yii::$app->user->identity->getAuthKey(),
            'username' => Yii::$app->user->identity->username,
//            'user_id' => Yii::$app->user->identity->user_id,
            'status' => Yii::$app->user->identity->status,

        ];
        return $response;

    }


    public function actionReceive()
    {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

        $data = file_get_contents('php://input');
        if ($data != null) {
            $model = new IncomingSalesData();
            $model->sales_data = $data;
            $model->status = IncomingSalesData::DATA_RECEIVED;
            $model->date_time = date('Y-m-d H:i:s');
            if($model->save(false))
            {
                $Json = $model['sales_data'];

                //converts json data to array data
                $decodedSales = json_decode($Json, true);



                //Extracting API Request data
                $protocol =  $decodedSales['PROTOCOL'];
                if($protocol == 'WEBVFD') {

                    //Creates DATE AND TIME
                    //<DATE>2018-11-07</DATE>
                    //<TIME>16:10:30</TIME>
                    //==============================================================================
                    $datetime = $decodedSales['DATETIME'];
                    $time = date('H:i:s', strtotime($datetime));
                    $date = date('Y-m-d', strtotime($datetime));
                    $Date = ['DATE' => $date];
                    $Time = ['TIME' => $time];

                    //Creates Company details
                    //<TIN>109029092</TIN>
                    //<REGID>TZ001999000001</REGID>
                    //<EFDSERIAL>01TZ000001</EFDSERIAL
                    //==============================================================================
                    $companyCode = $decodedSales['CODE'];
                    $companyDetails = Company::find()->where(['tin' => $companyCode])->one();
                    $regId = $companyDetails->registration_id;
                    $efdSerial = $companyDetails->serial_number;
                    $tin = $companyDetails->tin;
                    $company = ['TIN' => $tin, 'REGID' => $regId, 'EFDSERIAL' => $efdSerial];


                    //Creates Customer details
                    //<CUSTIDTYPE>1</CUSTIDTYPE>
                    //<CUSTID>111222333</CUSTID>
                    //<CUSTNAME>Richard Kazimoto</CUSTNAME>
                    //<MOBILENUM>0713655545</MOBILENUM>
                    //==============================================================================
                    $customerDetails = $decodedSales['CUSTOMER'];
                    $customer = [
                        'CUSTIDTYPE' => $customerDetails['CUSTIDTYPE'],
                        'CUSTID' => $customerDetails['CUSTID'],
                        'CUSTNAME' => $customerDetails['CUSTNAME'],
                        'MOBILENUM' => $customerDetails['MOBILENUM'],
                    ];

                    //Creates Receipt Details/header
                    //<RCTNUM>1</RCTNUM>
                    //<DC>9</DC>
                    //<GC>1098</GC>
                    //<ZNUM>100</ZNUM>
                    //<RCTVNUM>AAAA119</RCTVNUM>
                    //==============================================================================

                    $dailyCounter = WebVfdApi::getDailyCounterByCompanyCode($companyDetails->id, $date);
                    $globalCounter = WebVfdApi::getGlobalCounterByCompanyCode($companyDetails->id);
                    $RCTVNUM = $companyDetails->receipt_number;
                    $fiscal_code = $RCTVNUM . $globalCounter;
                    $RCTNUM = $globalCounter;
                    $znum = date('Ymd');

                    $receipt = ['RCTNUM' => $RCTNUM, 'DC' => $dailyCounter, 'GC' => $globalCounter, 'ZNUM' => $znum, 'RCTVNUM' => $fiscal_code];


                    //creates receipt items
                    //=============================================================================

                    $items = $decodedSales['ITEMS'];

                    foreach ($items as $key => $item) {

                        $sold_items[] =
                            [
                                'ITEM' => [
                                    'ID' => $item['ID'],
                                    'DESC' => $item['DESC'],
                                    'QTY' => $item['QTY'],
                                    'TAXCODE' => $item['TAXCODE'],
                                    'AMT' => $item['AMT'],
                                ]
                            ];
                    }
                    $items = ['ITEMS' => $sold_items];

                    $taxPercent = Taxconfig::find()->where(['company_id' => $companyDetails->id])->one();

                    $vatRate = $taxPercent['taxPercent'];


                    //Add Sales Totals to receipt
                    //<TOTALS>
                    //<TOTALTAXEXCL>18000.00</TOTALTAXEXCL>
                    //<TOTALTAXINCL>38000.0</TOTALTAXINCL>
                    //<DISCOUNT>0.00</DISCOUNT>
                    //</TOTALS>
                    //=============================================================================
                    $untaxedAmount = $decodedSales['UNTAXEDTOTAL'];
                    $taxAmount = $decodedSales['TOTALTAX'];
                    $netAmount = $decodedSales['TAXEDTOTAL'];
                    $discount = 0.00;

                    //Creates Totals Arrays
                    $total = ['TOTALTAXEXCL' => $untaxedAmount, 'TOTALTAXINCL' => $netAmount, 'DISCOUNT' => $discount];


                    //Add payments details to receipt
                    //<PAYMENTS>
                    //<PMTTYPE>CASH</PMTTYPE>
                    //<PMTAMOUNT>50000.00</PMTAMOUNT>
                    //<PMTTYPE>CHEQUE</PMTTYPE>
                    //<PMTAMOUNT>100000.00</PMTAMOUNT>
                    //<PMTTYPE>CCARD</PMTTYPE>
                    //<PMTAMOUNT>68000.00</PMTAMOUNT>
                    //<PMTTYPE> EMONEY</PMTTYPE>
                    //<PMTAMOUNT>68000.00</PMTAMOUNT>
                    //<PMTTYPE> INVOICE </PMTTYPE>
                    //<PMTAMOUNT>68000.00</PMTAMOUNT>
                    //</PAYMENTS>
                    //=============================================================================
                    $pmtType = 'CASH';
                    $pmtAmount = $netAmount;
                    $payment = ['PMTTYPE' => $pmtType, 'PMTAMOUNT' => $pmtAmount];


                    //<VATTOTALS>
                    //<VATRATE>A</VATRATE>
                    //<NETTAMOUNT>100000.00</NETTAMOUNT>
                    //<TAXAMOUNT>16500.00</TAXAMOUNT>
                    //<VATRATE>B</VATRATE>
                    //<NETTAMOUNT>100000.00</NETTAMOUNT>
                    //<TAXAMOUNT>0.00</TAXAMOUNT>
                    //<VATRATE>C</VATRATE>
                    //<NETTAMOUNT>100000.00</NETTAMOUNT>
                    //<TAXAMOUNT>0.00</TAXAMOUNT>
                    //</VATTOTALS>
                    $vatTotal = ['VATRATE' => 'A', 'NETTAMOUNT' => $netAmount, 'TAXAMOUNT' => $taxAmount];


                    $totalsPaymentsVrates = ['TOTALS' => $total, 'PAYMENTS' => $payment, 'VATTOTALS' => $vatTotal];


                    $arrayReceipt = array_merge($Date, $Time, $company, $customer, $receipt, $items, $totalsPaymentsVrates);
                    $rootTag = '<RCT/>';
                    $SalesItemsXml = WebVfdApi::convertToXml($arrayReceipt, $root = null, $xml = null,$rootTag);
                    $xml_converted = trim(str_replace('<?xml version="1.0"?>', '', $SalesItemsXml));

                    //removing the array keys sold items and the same to remove in the created xml
                    $item_key_data = array_keys($sold_items);
                    $itemsArraySize = count($sold_items);
                    for ($i = 0; $i < $itemsArraySize; $i++) {
                        $string = $item_key_data[$i] . ',';
                        $string_serialize = rtrim($string, ", ");
                        $xml_converted = trim(str_replace("<$string_serialize>", '', $xml_converted));
                        $xml_converted = trim(str_replace("</$string_serialize>", '', $xml_converted));
                    }


                    //Get securities parameters

                    $access_token = WebVfdApi::getApiToken($companyDetails->id);
                    $private_key = WebVfdApi::getPrivateKey($companyDetails->id);

                    //create required xml format
                    $xml_doc = "<?xml version='1.0' encoding='UTF-8'?>";
                    $efdms_open = "<EFDMS>";
                    $efdms_close = "</EFDMS>";


                    $isGenerated2 = openssl_sign($xml_converted, $signature, $private_key, OPENSSL_ALGO_SHA1);
                    openssl_free_key($private_key);
                    if (!$isGenerated2) {
                        throw new \RuntimeException(('Computing of the signature failed'));
                    }
                    $signature_value = base64_encode($signature);
                    $efdmsSignature = "<EFDMSSIGNATURE>$signature_value</EFDMSSIGNATURE>";
                    $signedData = $xml_doc . $efdms_open . $xml_converted . $efdmsSignature . $efdms_close;




                    $resultEfd = WebVfdApi::issueReceipt($access_token, $signedData,$companyDetails->id);
                    $data = new SimpleXMLElement($resultEfd);
                    $responseStatus = (string)$data->RCTACK[0]->ACKMSG;

                    if ($responseStatus == 'Success') {

                        $logs = new TraLogs();
                        $logs->status = 'SUCCESS';
                        $logs->action = 'SENDING RECEIPT DATA';
                        $logs->datetime = date('Y-m-d H:i:s');
                        $logs->module = 'ONLINE';
                        $logs->znumber = date('Ymd');
                        $logs->message = "SENDING RECEIPT DATA SUCCESS";
                        $logs->save(false);


                        $zreport = new ZReportData();
                        $zreport->datetime = $datetime;
                        $zreport->znumber = $znum;
                        $zreport->nettamount = $netAmount;
                        $zreport->company_id = $companyDetails->id;
                        $zreport->pmtamount = $netAmount;
                        $zreport->fiscal_code = $fiscal_code;
                        $zreport->discount = $discount;
                        $zreport->pmttype = $pmtType;
                        $zreport->taxamount = $taxAmount;
                        $zreport->vatrate = $vatRate;
                        $zreport->status = ZReportData::CREATED_DATA;
                        $zreport->save(false);


                        $daily = new DailyCounter();
                        $daily->reference_no = $dailyCounter;
                        $daily->company_id = $companyDetails->id;
                        $daily->created_at = date('Y-m-d');
                        $daily->created_by = '';
                        $daily->save(false);

                        $global = new GlobalCounter();
                        $global->reference_no = $globalCounter;
                        $global->company_id = $companyDetails->id;
                        $global->created_at = date('Y-m-d');
                        $global->created_by = '';
                        $global->save(false);


                        $receiptData = new ReceiptData();
                        $receiptData->receipt_data = $signedData;
                        $receiptData->company_id = $companyDetails->id;
                        $receiptData->fiscal_code = $fiscal_code;
                        $receiptData->transaction_id = $fiscal_code;
                        $receiptData->status = ReceiptData::DATA_SENT;
                        $receiptData->response_status = $responseStatus;
                        $receiptData->response_ack = $resultEfd;
                        $receiptData->created_at = date('Y-m-d H:i:s');
                        $receiptData->save(false);

                        //FOR VERIFY RECEIPT
                        $verifyReceiptURL = UrlConfig::getUrlByName(WebVfdApi::VERIFY_RECEIPT);
                        $verifyReceiptURL = $verifyReceiptURL->url;
                        $qrCode = $verifyReceiptURL . $fiscal_code . '_' . $time;

                        IncomingSalesData::updateAll(['status' => IncomingSalesData::DATA_SENT,'qrCode' =>$qrCode,  'company_id' => $companyDetails->id],['id' => $model->id]);


                        $response = [
                            'COMPANY' => [
                                'NAME' => $companyDetails->name,
                                'ADDRESS' => $companyDetails->address,
                                'MOBILE' => $companyDetails->contact_person,
                                'TIN' => $companyDetails->tin,
                                'VRN' => $companyDetails->vrn,
                                'SERIAL_NO' => $companyDetails->serial_number,
                                'UIN' => $companyDetails->uin,
                                'TAX_OFFICE' => $companyDetails->tax_office,
                            ],
                            'RECEIPT' => [
                                'RECEIPT_NO' => $RCTNUM,
                                'Z_NO' => $znum,
                                'DATE' => $date,
                                'TIME' => $time,
                            ],
                            'QR_CODE' => $qrCode,
                            'status' => 200

                        ];
                        return $response;

                    }
                }else{
                    //Not from our protocol
                }























            }

        }else{

            //no json data received
        }

    }

}