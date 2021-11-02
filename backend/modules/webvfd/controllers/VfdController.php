<?php

namespace backend\controllers;


use backend\models\Company;
use backend\models\Customers;
use backend\models\DailyCounter;
use backend\models\GlobalCounter;
use backend\models\Product;
use backend\models\ProductCategory;
use backend\models\ProductType;
use backend\models\Purchases;
use backend\models\ReceiptData;
use backend\models\RenewLicence;
use backend\models\Sales;
use backend\models\Setting;
use backend\models\SoldItems;
use backend\models\Status;
use backend\models\Stock;
use backend\models\StockBalance;
use backend\models\Store;
use backend\models\TaxCategory;
use backend\models\Taxconfig;
use backend\models\Transactions;
use backend\models\User;
use backend\models\UserTypes;
use backend\models\ValidLicence;
use backend\models\Warehouse;
use Exception;
use frontend\models\SignupForm;
use SimpleXMLElement;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use const http\Client\Curl\Versions\CURL;


class VfdController extends \yii\rest\ActiveController
{
    public $modelClass = 'backend\models\TransportFees';

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




    ######### NEW SALE #############
    public function actionNewSale()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $data = json_decode(file_get_contents("php://input"));

        $data_array = $data->Sales;
        $items = json_decode(json_encode($data_array), True);
        $keys = array_keys($items);
        $arraySize = count($items);

        for ($i = 0; $i < $arraySize; $i++) {

            $string = $keys[$i] . ',';
            $string_serialize = rtrim($string, ", ");
            $array = explode(' ', $string_serialize);

            foreach ($array as $key => $single) {

                $check_sale = Sales::findOne(['sale_ref_no' => $items[$single]['sale_ref_no']]);

                if (empty($check_sale)) {

                    $model = new Sales();
                    $model->sale_ref_no = $items[$single]['sale_ref_no'];
                    $model->cashier_id = $items[$single]['cashier_id'];
                    $model->total = $items[$single]['total'];
                    $model->store_id = $items[$single]['store_id'];
                    $model->status = 1;
                    $model->created_by = $items[$single]['cashier_id'];
                    $model->date = date('Y-m-d');
                    $model->created_at = date('Y-m-d H:i:s');
                    $item_data = $items[$single]['items'];
                    $store = Store::findOne(['id' => $items[$single]['store_id']]);
                    $warehouse = Warehouse::findOne(['id' => $store->warehouse_id]);
                    $company = Company::findOne(['id' => $warehouse->company_id]);
                    $company_id = $company->id;
                    $username = $company->company_username;
                    $password = $company->password;
                    $certificate_password = $company->certificate_password;
                    $cs = $company->certificate_serial;
                    $current_date = date('Y-m-d');
                    $current_time = date('H:i:s');
                    $tin = $company->tin;
                    $regId = $company->registration_id;
                    $efdSerial = $company->serial_number;
                    $RCTVNUM = $company->receipt_number;
                    $RCTNUM = 3;
                    $znum = date('Ymd');
                    $pmtAmount = $items[$single]['total'];

                    //SAVING CUSTOMERS DETAILS
                    $modelCustomer = new Customers();
                    $modelCustomer->company_id = $company_id;
                    $modelCustomer->customerName = $items[0]['Customer']['customerName'];
                    $modelCustomer->customerPhone = $items[0]['Customer']['customerPhone'];
                    $modelCustomer->customerCode = $items[0]['Customer']['customerCode'];
                    $modelCustomer->customerAddress = $items[0]['Customer']['customerAddress'];
                    $modelCustomer->customerCodeType = $items[0]['Customer']['customerCodeType'];
                    $modelCustomer->customerEmail = $items[0]['Customer']['customerEmail'];
                    $modelCustomer->created_by = $model->cashier_id;
                    $modelCustomer->created_at = date('Y-m-d H:i:s');
                    $modelCustomer->save(false);

                    $custType = $modelCustomer->customerCodeType;
                    $custId = $modelCustomer->customerCode;
                    $model->customer_id = $modelCustomer->id;
                    $custName = $modelCustomer->customerName;
                    $mobile = $modelCustomer->customerPhone;
                    $email = $modelCustomer->customerEmail;

                    if (!empty($company->file) && !empty($company->certificate_password)) {

                        if ($model->save()) {

                            $key_data = array_keys($item_data);
                            $arraySize = count($item_data);
                            for ($i = 0; $i < $arraySize; $i++) {
                                $string = $key_data[$i] . ',';
                                $string_serialize = rtrim($string, ", ");
                                $array_data = explode(' ', $string_serialize);

                                foreach ($array_data as $k => $single_data) {
                                    $product_id = Product::findOne(['product_ref_no' => $item_data[$single_data]['ref_no']]);

                                    $items_model = new SoldItems();
                                    $items_model->sale_id = $model->id;
                                    $items_model->product_id = $product_id->id;
                                    //  $items_model->product_id = $item_data[$single_data]['ID'];
                                    $items_model->taxcode = $item_data[$single_data]['TAXCODE'];
                                    $items_model->amount = $item_data[$single_data]['AMT'];
                                    $items_model->quantity = $item_data[$single_data]['QTY'];
                                    $items_model->name = $item_data[$single_data]['DESC'];
                                    $items_model->status = 1;
                                    $items_model->created_by = $items[$single]['cashier_id'];
                                    $items_model->created_at = date('Y-m-d H:i:s');
                                    $items_model->save();
                                    $id = $item_data[$single_data]['ID'];
                                    $desc = $item_data[$single_data]['DESC'];
                                    $qty = $item_data[$single_data]['QTY'];
                                    $taxCode = 3;
                                    $amount = $item_data[$single_data]['AMT'];
                                    $sold_items_data[] =
                                        ['ITEM' => [
                                            'ID' => $id,
                                            'DESC' => $desc,
                                            'QTY' => $qty,
                                            'TAXCODE' => $taxCode,
                                            'AMT' => $amount,
                                        ]];

                                    $stock = StockBalance::findOne(['product_id' => $product_id->id]);
                                    StockBalance::updateAll(
                                        [
                                            'quantity' => $stock->quantity - $items_model->quantity,
                                            'price' => $stock->price - ($items_model->amount * $items_model->quantity)
                                        ],
                                        ['product_id' => $product_id->id]);

                                }
                            }

                            $model = DailyCounter::find()
                                ->where(['company_id' => $company_id])
                                ->max('reference_no');

                            $check_last = DailyCounter::find()
                                ->where(['company_id' => $company_id])
                                ->orderBy(['id' => SORT_DESC])
                                ->one();

                            if ($model != '') {

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

                            $vatRate = 18.00;
                            $desc = 0.00;
                            $amnt = 0.00;

                            $totalTaxExcl = 10.00;
                            $totalTaxIncl = 0.00;
                            $discount = 0.00;
                            $pmtType = 'CASH';
                            $array_item_data = $sold_items_data;
                            $vatTotal = array('VATRATE' => $vatRate,
                                'NETTAMOUNT' => $desc,
                                'TAXAMOUNT' => $amnt
                            );
                            $total = array('TOTALTAXEXCL' => $totalTaxExcl,
                                'TOTALTAXINCL' => $totalTaxIncl,
                                'DISCOUNT' => $discount
                            );
                            $payment = array('PMTTYPE' => $pmtType,
                                'PMTAMOUNT' => $pmtAmount
                            );

                            $ref = $items[$single]['store_id'] . $company->tin . $global_counter;
                            $fiscal_code = $RCTVNUM . $global_counter;

                            //  $urlToken = 'https://196.43.230.13/efdmsRctApi/vfdtoken';
                            $urlToken = 'https://virtual.tra.go.tz/efdmsRctApi/vfdtoken';
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
                            $resultToken = curl_exec($curlToken);
                            if (curl_errno($curlToken)) {
                                throw new \Exception(curl_error($curlToken));
                            }
                            curl_close($curlToken);
                            $at = json_decode($resultToken, true);
                            $access_token = $at['access_token'];
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
                            //    $data = "<RCT><DATE>2020-09-28</DATE><TIME>09:00:57</TIME><TIN>133464514</TIN><REGID>TZ0100551181</REGID><EFDSERIAL>10TZ100392</EFDSERIAL><CUSTIDTYPE>6</CUSTIDTYPE><CUSTID></CUSTID><CUSTNAME>Joshua Nelson</CUSTNAME><MOBILENUM>0716601283</MOBILENUM><RCTNUM>3</RCTNUM><DC>1</DC><GC>147</GC><ZNUM>20200928</ZNUM><RCTVNUM>80735D147</RCTVNUM><ITEMS><ITEM><ID>1</ID><DESC>TODAY test</DESC><QTY>1</QTY><TAXCODE>3</TAXCODE><AMT>10.00</AMT></ITEM></ITEMS><TOTALS><TOTALTAXEXCL>10.00</TOTALTAXEXCL><TOTALTAXINCL>0.00</TOTALTAXINCL><DISCOUNT>0.00</DISCOUNT></TOTALS><PAYMENTS><PMTTYPE>CASH</PMTTYPE><PMTAMOUNT>10.00</PMTAMOUNT></PAYMENTS><VATTOTALS><VATRATE>C</VATRATE><NETTAMOUNT>10.00</NETTAMOUNT><TAXAMOUNT>0.00</TAXAMOUNT></VATTOTALS></RCT>";

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

                            $isGenerated2 = openssl_sign($xml_converted, $signature, $pkeyid, OPENSSL_ALGO_SHA1);
                            openssl_free_key($pkeyid);
                            if (!$isGenerated2) {
                                throw new \RuntimeException(('Computing of the signature failed'));
                            }
                            $signature_value = base64_encode($signature);
                            $efdmsSignature = "<EFDMSSIGNATURE>$signature_value</EFDMSSIGNATURE>";
                            $signedData = $xml_doc . $efdms_open . $xml_converted . $efdmsSignature . $efdms_close;

                            /*   print_r($signedData);
                                  die;*/
                            // $urlReceipt = 'https://196.43.230.13/efdmsRctApi/api/efdmsRctInfo';
                            $urlReceipt = 'https://virtual.tra.go.tz/efdmsRctApi/api/efdmsRctInfo';
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
                            $resultEfd = curl_exec($curl);
                            if (curl_errno($curl)) {
                                throw new \Exception(curl_error($curl));
                            }
                            curl_close($curl);
                            // $efdms_response = XmlTo::convert($resultEfd, $outputRoot = false);
                            $data = new SimpleXMLElement($resultEfd);
                            $responseStatus = (string)$data->RCTACK[0]->ACKMSG;

//                            print_r($responseStatus);
//                            die();

                            $daily = new DailyCounter();
                            $daily->reference_no = $daily_counter;
                            $daily->company_id = $company->id;
                            $daily->created_at = date('Y-m-d');
                            $daily->created_by = $items[$single]['cashier_id'];
                            $daily->save(false);

                            $global = new GlobalCounter();
                            $global->reference_no = $global_counter;
                            $global->company_id = $company->id;
                            $global->created_at = date('Y-m-d');
                            $global->created_by = $items[$single]['cashier_id'];
                            $global->save(false);

                            //CHECK IF TRA SERVER IS AVAILABLE
                            $server = "virtual.tra.go.tz";
                            if(checkdnsrr($server, "ANY")){


                                if ($responseStatus == "Success") {

                                    $trans = new Transactions();
                                    $trans->customer_id = $modelCustomer->id;
                                    $trans->sale_id = $items_model->sale_id;
                                    $trans->ref_no = $items[$single]['store_id'] . $company->tin . $global_counter;
                                    $trans->transaction_date = date('Y-m-d H:i:s');
                                    $trans->amount_in = $items[$single]['total'];
                                    $trans->amount_out = 0;
                                    $trans->payment_method = 1;
                                    $trans->fiscal_code = $RCTVNUM . $global_counter;
                                    $trans->created_by = $items[$single]['cashier_id'];
                                    $trans->created_at = date('Y-h-m H:i:s');
                                    $trans->status = Transactions::DATA_SENT;
                                    $trans->save(false);

                                    $receiptData = new ReceiptData();
                                    $receiptData->receipt_data = $signedData;
                                    $receiptData->company_id = $company_id;
                                    $receiptData->transaction_id = $trans->id;
                                    $receiptData->status = ReceiptData::DATA_SENT;
                                    $receiptData->response_status = $responseStatus;
                                    $receiptData->created_at = date('Y-m-d H:i:s');
                                    $receiptData->save();

                                    $response_data['reference'] = $ref;
                                    $response_data['receiptID'] = $trans->id;
                                    $response_data['receiptQRData'] = "https://virtual.tra.go.tz/efdmsRctVerify/" . $fiscal_code;

                                    return Json::encode($response_data);

                                } else {

                                    $trans = new Transactions();
                                    $trans->customer_id = $modelCustomer->id;
                                    $trans->sale_id = $items_model->sale_id;
                                    $trans->ref_no = $items[$single]['store_id'] . $company->tin . $global_counter;
                                    $trans->transaction_date = date('Y-m-d H:i:s');
                                    $trans->amount_in = $items[$single]['total'];
                                    $trans->amount_out = 0;
                                    $trans->payment_method = 1;
                                    $trans->fiscal_code = $RCTVNUM . $global_counter;
                                    $trans->created_by = $items[$single]['cashier_id'];
                                    $trans->created_at = date('Y-h-m H:i:s');
                                    $trans->status = Transactions::DATA_NOT_SENT;

                                    $receiptData = new ReceiptData();
                                    $receiptData->receipt_data = $signedData;
                                    $receiptData->company_id = $company_id;
                                    $receiptData->transaction_id = $trans->id;
                                    $receiptData->status = ReceiptData::DATA_NOT_SENT;
                                    $receiptData->response_status = $responseStatus;
                                    $receiptData->created_at = date('Y-m-d H:i:s');

                                    $is_exist = ReceiptData::find()->where(['id' => $receiptData->id])->one();
                                    $is_existTrans = Transactions::find()->where(['id' => $trans->id])->one();

                                    if (empty($is_exist)) {

                                        $receiptData->save(false);

                                        if (empty($is_existTrans)) {

                                            $trans->save(false);

                                        } else {

                                            Transactions::updateAll([
                                                'customer_id' => $modelCustomer->id,
                                                'sale_id' => $items_model->sale_id,
                                                'ref_no' => $items[$single]['store_id'] . $company->tin . $global_counter,
                                                'transaction_date' => date('Y-m-d H:i:s'),
                                                'amount_in' => $items[$single]['total'],
                                                'amount_out' => 0,
                                                'payment_method' => 1,
                                                'fiscal_code' => $RCTVNUM . $global_counter,
                                                'status' => Transactions::DATA_NOT_SENT,
                                                'created_at' => date('Y-m-d H:i:s'),
                                            ],
                                                ['id' => $trans->id]);

                                        }


                                    } else {

                                        ReceiptData::updateAll([
                                            'receipt_data' => $signedData,
                                            'status' => ReceiptData::DATA_NOT_SENT,
                                            'response_status' => $responseStatus,
                                            'created_at' => date('Y-m-d H:i:s'),
                                        ],

                                            ['id' => $is_exist->id]);

                                        if (empty($is_existTrans)) {

                                            $trans->save(false);

                                        } else {

                                            Transactions::updateAll([
                                                'customer_id' => $modelCustomer->id,
                                                'sale_id' => $items_model->sale_id,
                                                'ref_no' => $items[$single]['store_id'] . $company->tin . $global_counter,
                                                'transaction_date' => date('Y-m-d H:i:s'),
                                                'amount_in' => $items[$single]['total'],
                                                'amount_out' => 0,
                                                'payment_method' => 1,
                                                'fiscal_code' => $RCTVNUM . $global_counter,
                                                'status' => Transactions::DATA_NOT_SENT,
                                                'created_at' => date('Y-m-d H:i:s'),
                                            ],
                                                ['id' => $trans->id]);

                                        }

                                    }

                                    // $response_data['error'] = false;
                                    //  $response_data['status'] = 'success';
                                    // $response_data['reference'] = $ref;
                                    $response_data['reference'] = $ref;
                                    $response_data['receiptID'] = $trans->id;
                                    $response_data['receiptQRData'] = "https://virtual.tra.go.tz/efdmsRctVerify/" . $fiscal_code;
                                    //  $response_data['message'] = 'Successfully Sent and saved';
                                    return Json::encode($response_data);

                                }

                            }else{

                                $trans = new Transactions();
                                $trans->customer_id = $modelCustomer->id;
                                $trans->sale_id = $items_model->sale_id;
                                $trans->ref_no = $items[$single]['store_id'] . $company->tin . $global_counter;
                                $trans->transaction_date = date('Y-m-d H:i:s');
                                $trans->amount_in = $items[$single]['total'];
                                $trans->amount_out = 0;
                                $trans->payment_method = 1;
                                $trans->fiscal_code = $RCTVNUM . $global_counter;
                                $trans->created_by = $items[$single]['cashier_id'];
                                $trans->created_at = date('Y-h-m H:i:s');
                                $trans->status = Transactions::DATA_NOT_SENT;

                                $receiptData = new ReceiptData();
                                $receiptData->receipt_data = $signedData;
                                $receiptData->company_id = $company_id;
                                $receiptData->transaction_id = $trans->id;
                                $receiptData->status = ReceiptData::SERVER_IS_OFFLINE;
                                $receiptData->response_status = "TRA Server is offline";
                                $receiptData->created_at = date('Y-m-d H:i:s');

                                $is_exist = ReceiptData::find()->where(['id' => $receiptData->id])->one();
                                $is_existTrans = Transactions::find()->where(['id' => $trans->id])->one();

                                if (empty($is_exist)) {

                                    $receiptData->save(false);

                                    if (empty($is_existTrans)) {

                                        $trans->save(false);

                                    } else {

                                        Transactions::updateAll([
                                            'customer_id' => $modelCustomer->id,
                                            'sale_id' => $items_model->sale_id,
                                            'ref_no' => $items[$single]['store_id'] . $company->tin . $global_counter,
                                            'transaction_date' => date('Y-m-d H:i:s'),
                                            'amount_in' => $items[$single]['total'],
                                            'amount_out' => 0,
                                            'payment_method' => 1,
                                            'fiscal_code' => $RCTVNUM . $global_counter,
                                            'status' => Transactions::DATA_NOT_SENT,
                                            'created_at' => date('Y-m-d H:i:s'),
                                        ],
                                            ['id' => $trans->id]);

                                    }


                                } else {

                                    ReceiptData::updateAll([
                                        'receipt_data' => $signedData,
                                        'status' => ReceiptData::DATA_NOT_SENT,
                                        'response_status' => $responseStatus,
                                        'created_at' => date('Y-m-d H:i:s'),
                                    ],

                                        ['id' => $is_exist->id]);

                                    if (empty($is_existTrans)) {

                                        $trans->save(false);

                                    } else {

                                        Transactions::updateAll([
                                            'customer_id' => $modelCustomer->id,
                                            'sale_id' => $items_model->sale_id,
                                            'ref_no' => $items[$single]['store_id'] . $company->tin . $global_counter,
                                            'transaction_date' => date('Y-m-d H:i:s'),
                                            'amount_in' => $items[$single]['total'],
                                            'amount_out' => 0,
                                            'payment_method' => 1,
                                            'fiscal_code' => $RCTVNUM . $global_counter,
                                            'status' => Transactions::DATA_NOT_SENT,
                                            'created_at' => date('Y-m-d H:i:s'),
                                        ],
                                            ['id' => $trans->id]);

                                    }

                                }

                                $response_data['reference'] = $ref;
                                $response_data['receiptID'] = $trans->id;
                                $response_data['receiptQRData'] = "https://virtual.tra.go.tz/efdmsRctVerify/" . $fiscal_code;

                                return Json::encode($response_data);
                            }

                        }

                    } else {

                        $response['error'] = false;
                        $response['status'] = 'success';
                        $response['message'] = 'Customer Registration is not complete, please try again';
                        return Json::encode($response);
                    }

                } else {

                    $response['error'] = false;
                    $response['status'] = 'success';
                    $response['message'] = 'Sales Reference exist';
                    return Json::encode($response);

                }

            }


        }


    }


    public function actionZReport()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;

        $TIN = Yii::$app->request->post('TIN');

        $company = Company::find()->where(['tin' => $TIN])->one();

        $company_id = $company->id;
        $username = $company->company_username;
        $password = $company->password;
        $certificate_password = $company->certificate_password;
        $cs = $company->certificate_serial;
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        $tin = $company->tin;
        $uin = $company->uin;
        $custType = 1;
        $custId = 1;
        $custName = "Salim Kondo";
        $mobile = "0657699266";
        $email = "kondo@gmail.com";
        $regId = $company->registration_id;
        $efdSerial = $company->serial_number;
        $RCTVNUM = $company->receipt_number;
        $RCTNUM = 3;
        $znum = date('Ymd');
        $certificate_file = "file/144181875202101081143.pfx";

        $time = date("H:m:s");
        $CERTKEY = "10TZ100523";

        $encodedSerial = base64_encode($efdSerial);
        $headers = array(
            'Content-type: Application/xml',
            'Cert-Serial: ' . $encodedSerial,
            'Client: webapi ',
        );

        $encodedSerial = base64_encode($cs);
        $headers = array(
            'Content-type: Application/xml',
            'Cert-Serial: ' . $encodedSerial,
            'Client: webapi ',
        );

        /*  $arrayReceipt = array(
              'DATE'=>$current_date,
              'TIME'=>$current_time,
              'HEADER'=>array(
                   'LINE'=>$company->name,
              '<LINE>'KINONDONI SHAMBA</LINE>
              <LINE>TEL NO:+255 767 241 905</LINE>
              <LINE>DAR ES SALAAM,TANZANIA</LINE>
                  ),
              <VRN>NOT REGISTERED</VRN>
              <TIN>$tin</TIN>
              <TAXOFFICE>Tax Office Kinondoni</TAXOFFICE>
              <REGID>TZ0100552387</REGID>
              <ZNUMBER>$znum</ZNUMBER>
              <EFDSERIAL>10TZ100523</EFDSERIAL>
              <REGISTRATIONDATE>2021-01-08</REGISTRATIONDATE>
              <USER>$uin</USER>
              <SIMIMSI>WEBAPI</SIMIMSI>
              <TOTALS>
              <DAILYTOTALAMOUNT>200.00</DAILYTOTALAMOUNT>
              <GROSS>190.00</GROSS>
              <CORRECTIONS>0.00</CORRECTIONS>
              <DISCOUNTS>0.00</DISCOUNTS>
              <SURCHARGES>0.00</SURCHARGES>
              <TICKETSVOID>0</TICKETSVOID>
              <TICKETSVOIDTOTAL>0.00</TICKETSVOIDTOTAL>
              <TICKETSFISCAL>36</TICKETSFISCAL>
              <TICKETSNONFISCAL>6</TICKETSNONFISCAL>
              </TOTALS>
              <VATTOTALS>
              <VATRATE>A-18.00</VATRATE>
              <NETTAMOUNT>1816313.55</NETTAMOUNT>
              <TAXAMOUNT>326936.45</TAXAMOUNT>
              <VATRATE>B-0.00</VATRATE>
              <NETTAMOUNT>0.00</NETTAMOUNT>
              <TAXAMOUNT>0.00</TAXAMOUNT>
              <VATRATE>C-0.00</VATRATE>
              <NETTAMOUNT>0.00</NETTAMOUNT>
              <TAXAMOUNT>0.00</TAXAMOUNT>
              <VATRATE>D-0.00</VATRATE>
              <NETTAMOUNT>0.00</NETTAMOUNT>
              <TAXAMOUNT>0.00</TAXAMOUNT>
              <VATRATE>E-0.00</VATRATE>
              <NETTAMOUNT>0.00</NETTAMOUNT>
              <TAXAMOUNT>0.00</TAXAMOUNT>
              </VATTOTALS>
              <PAYMENTS>
              <PMTTYPE>CASH</PMTTYPE>
              <PMTAMOUNT>21.00</PMTAMOUNT>
              <PMTTYPE>CHEQUE</PMTTYPE>
              <PMTAMOUNT>0.00</PMTAMOUNT>
              <PMTTYPE>CCARD</PMTTYPE>
              <PMTAMOUNT>0.00</PMTAMOUNT>
              <PMTTYPE>EMONEY</PMTTYPE>
              <PMTAMOUNT>0.00</PMTAMOUNT>
              <PMTTYPE>INVOICE</PMTTYPE>
              <PMTAMOUNT>0.00</PMTAMOUNT>
              </PAYMENTS>
              <CHANGES>
              <VATCHANGENUM>0</VATCHANGENUM>
              <HEADCHANGENUM>0</HEADCHANGENUM>
              </CHANGES>
              <ERRORS></ERRORS>
              <FWVERSION>3.0</FWVERSION>
              <FWCHECKSUM>WEBAPI</FWCHECKSUM>
          );*/

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

        $cert_store = file_get_contents($certificate_file);
        $cert_info = array();

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
        $isGenerated2 = openssl_sign($xml_converted, $signature, $pkeyid, OPENSSL_ALGO_SHA1);

        openssl_free_key($pkeyid);
        if (!$isGenerated2) {
            throw new \RuntimeException(('Computing of the signature failed'));
        }

        $signatureGenerated = base64_encode($signature);

        $data = "<?xml version='1.0' encoding='UTF-8'?><EFDMS>
                        <ZREPORT>
            <DATE>$current_date</DATE>
            <TIME>$current_time</TIME>
            <HEADER>
            <LINE>FUNDIPOOL LIMITED</LINE>
            <LINE>KINONDONI SHAMBA</LINE>
            <LINE>TEL NO:+255 767 241 905</LINE>
            <LINE>DAR ES SALAAM,TANZANIA</LINE>
            </HEADER>
            <VRN>NOT REGISTERED</VRN>
            <TIN>$tin</TIN>
            <TAXOFFICE>Tax Office Kinondoni</TAXOFFICE>
            <REGID>TZ0100552387</REGID>
            <ZNUMBER>$znum</ZNUMBER>
            <EFDSERIAL>10TZ100523</EFDSERIAL>
            <REGISTRATIONDATE>2021-01-08</REGISTRATIONDATE>
            <USER>$uin</USER>
            <SIMIMSI>WEBAPI</SIMIMSI>
            <TOTALS>
            <DAILYTOTALAMOUNT>200.00</DAILYTOTALAMOUNT>
            <GROSS>190.00</GROSS>
            <CORRECTIONS>0.00</CORRECTIONS>
            <DISCOUNTS>0.00</DISCOUNTS>
            <SURCHARGES>0.00</SURCHARGES>
            <TICKETSVOID>0</TICKETSVOID>
            <TICKETSVOIDTOTAL>0.00</TICKETSVOIDTOTAL>
            <TICKETSFISCAL>36</TICKETSFISCAL>
            <TICKETSNONFISCAL>6</TICKETSNONFISCAL>
            </TOTALS>     
            <VATTOTALS>
            <VATRATE>A-18.00</VATRATE>
            <NETTAMOUNT>1816313.55</NETTAMOUNT>
            <TAXAMOUNT>326936.45</TAXAMOUNT>
            <VATRATE>B-0.00</VATRATE>
            <NETTAMOUNT>0.00</NETTAMOUNT>
            <TAXAMOUNT>0.00</TAXAMOUNT>
            <VATRATE>C-0.00</VATRATE>
            <NETTAMOUNT>0.00</NETTAMOUNT>
            <TAXAMOUNT>0.00</TAXAMOUNT>
            <VATRATE>D-0.00</VATRATE>
            <NETTAMOUNT>0.00</NETTAMOUNT>
            <TAXAMOUNT>0.00</TAXAMOUNT>
            <VATRATE>E-0.00</VATRATE>
            <NETTAMOUNT>0.00</NETTAMOUNT>
            <TAXAMOUNT>0.00</TAXAMOUNT>
            </VATTOTALS>
            <PAYMENTS>
            <PMTTYPE>CASH</PMTTYPE>
            <PMTAMOUNT>21.00</PMTAMOUNT>
            <PMTTYPE>CHEQUE</PMTTYPE>
            <PMTAMOUNT>0.00</PMTAMOUNT>
            <PMTTYPE>CCARD</PMTTYPE>
            <PMTAMOUNT>0.00</PMTAMOUNT>
            <PMTTYPE>EMONEY</PMTTYPE>
            <PMTAMOUNT>0.00</PMTAMOUNT>
            <PMTTYPE>INVOICE</PMTTYPE>
            <PMTAMOUNT>0.00</PMTAMOUNT>
            </PAYMENTS>
            <CHANGES>
            <VATCHANGENUM>0</VATCHANGENUM>
            <HEADCHANGENUM>0</HEADCHANGENUM>
            </CHANGES>
            <ERRORS></ERRORS>
            <FWVERSION>3.0</FWVERSION>
            <FWCHECKSUM>WEBAPI</FWCHECKSUM>
        </ZREPORT>
                        <EFDMSSIGNATURE>$signatureGenerated</EFDMSSIGNATURE>
                    </EFDMS>";

        // $url = "https://196.43.230.13/efdmsRctApi/api/efdmszreport";
        $url = "https://virtual.tra.go.tz/efdmsRctApi/api/efdmszreport";
        $curl = curl_init($url);
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
        print_r($resultEfd);
        die();

    }


    protected function verbs()
    {
        return [
            'login' => ['POST'],
            'add-user' => ['POST'],
            'new-product-category' => ['POST'],
            'new-product-type' => ['POST'],
            'change-password' => ['POST'],
            'add-product' => ['POST'],
            'edit-product' => ['POST'],
            'remove-product' => ['POST'],
            'add-stock' => ['POST'],
            'new-sale' => ['POST'],
            'products' => ['GET'],
            'product-category' => ['GET'],
            'store' => ['GET'],
            'stocks' => ['GET'],
            'stock-balance' => ['GET'],
            'support' => ['GET'],
            'get-info' => ['POST'],

        ];
    }

}



