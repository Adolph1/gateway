<?php

namespace frontend\models;

use http\Exception;
use Yii;
use yii\helpers\Json;
use SimpleXMLElement;

/**
 * This is the model class for table "receipt_data".
 *
 * @property int $id
 * @property string $receipt_data
 * @property string $response_ack
 * @property string $fiscal_code
 * @property int $status
 * @property int $company_id
 * @property int $transaction_id
 * @property string $response_status
 * @property string $created_at
 */
class ReceiptData extends \yii\db\ActiveRecord
{

    const DATA_SENT = 1;
    const DATA_NOT_SENT = 0;
    const SERVER_IS_OFFLINE = 2;
    const INVALID_XML_DATA = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'receipt_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receipt_data', 'status'], 'required'],
            [['receipt_data', 'response_ack', 'response_status'], 'string'],
            [['status', 'company_id', 'transaction_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'receipt_data' => 'Receipt Data',
            'transaction_id' => 'Transaction ID',
            'response_status' => 'Response Status',
            'response_ack' => 'Response Acknowledgment',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    public static function makeAutomaticSendingDataToTRA()
    {

        //URL CONFIGURATION FROM BACKEND
        $url = UrlConfig::find()->where(['name' => 'INVOICE/RECEIPT'])->one();
        $domainName = $url['domain_name'];
        $hostURL = $url['url'];


        //for TOKEN REQUEST
        $tokenRequestURL = UrlConfig::find()->where(['name' => 'TOKEN REQUEST'])->one();


        if (checkdnsrr($domainName, "ANY")) {

            $offlineReceiptDatas = ReceiptData::find()
                ->where(['status' => ReceiptData::SERVER_IS_OFFLINE])
                ->all();

            if(!empty($offlineReceiptDatas)){
                foreach ($offlineReceiptDatas as $key => $single_data) {

                    $company = Company::find()->where(['id'=>$single_data->company_id])->one();

                    if(!empty($company)){

                        $username = $company->company_username;
                        $password = $company->password;
                        $cs = $company->certificate_serial;

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
                        $resultToken = curl_exec($curlToken);
                        if (curl_errno($curlToken)) {
                            throw new \Exception(curl_error($curlToken));
                        }
                        curl_close($curlToken);
                        $at = json_decode($resultToken, true);
                        $access_token = $at['access_token'];

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
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $single_data->receipt_data);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        $resultEfd = curl_exec($curl);
                        if (curl_errno($curl)) {
                            throw new \Exception(curl_error($curl));
                        }

                        curl_close($curl);

                        // $efdms_response = XmlTo::convert($resultEfd, $outputRoot = false);
                        $data = new \SimpleXMLElement($resultEfd);
                        $responseStatus = (string)$data->RCTACK[0]->ACKMSG;

                        if ($responseStatus == "Success") {

//                            Transactions::updateAll([
//                                'status' => Transactions::DATA_SENT,
//                            ],
//                                ['id' => $single_data->transaction_id]);

                            ReceiptData::updateAll([
                                'status' => ReceiptData::DATA_SENT,
                                'response_ack' => $resultEfd,
                                'response_status' => $responseStatus,
                            ],
                                ['id' => $single_data->id]);

                        }

                        else{
                            echo $resultEfd;
                            die;
                        }

                    }

                }

            }

            //RESEND ALL OFFLINE DATA TO TRA I.E NOT SIGNED DATA

            $url = UrlConfig::find()->where(['name' => 'INVOICE/RECEIPT'])->one();
            //  $domainName = $url['domain_name'];
            $ReceiptUrl = $url['url'];
            $offlineDatas = ReceiptData::find()
                ->where(['status' => ReceiptData::DATA_NOT_SENT])
                ->all();

            if (!empty($offlineDatas)) {

                foreach ($offlineDatas as $key => $single_data) {

                    $company = Company::find()->where(['id' => $single_data['company_id']])->one();
                    $username = $company->company_username;
                    $password = $company->password;
                    $cs = $company->certificate_serial;

                    $UnSignedData = $single_data['receipt_data'];

                    if (!empty($company)) {


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
                        $resultToken = curl_exec($curlToken);
                        if (curl_errno($curlToken)) {
                            throw new \Exception(curl_error($curlToken));
                        }
                        curl_close($curlToken);
                        $at = json_decode($resultToken, true);
                        $access_token = $at['access_token'];
                        $certificate_password = $company->certificate_password;
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


                        $isGenerated2 = openssl_sign($UnSignedData, $signature, $pkeyid, OPENSSL_ALGO_SHA1);
                        openssl_free_key($pkeyid);
                        if (!$isGenerated2) {
                            throw new \RuntimeException(('Computing of the signature failed'));
                        }
                        $signature_value = base64_encode($signature);
                        $xml_doc = "<?xml version='1.0' encoding='UTF-8'?>";
                        $efdms_open = "<EFDMS>";
                        $efdms_close = "</EFDMS>";
                        $efdmsSignature = "<EFDMSSIGNATURE>$signature_value</EFDMSSIGNATURE>";
                        $signedData = $xml_doc . $efdms_open . $UnSignedData . $efdmsSignature . $efdms_close;

                        $routing_key = 'vfdrct';
                        $headers = array(
                            'Content-type: Application/xml',
                            'Routing-Key: ' . $routing_key,
                            'Cert-Serial: ' . base64_encode($cs),
                            'Authorization: Bearer ' . $access_token,
                        );
                        $curl = curl_init($ReceiptUrl);
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
                        try {
                            $data = new SimpleXMLElement($resultEfd);
                            $responseStatus = (string)$data->RCTACK[0]->ACKMSG;
                        } catch (Exception $e) {
                            $response['error'] = false;
                            $response['status'] = 'fail';
                            $response['message'] = 'Receipt Url not reachable, try again';
                            return Json::encode($response);
                        }


                        if ($responseStatus == "Success") {

//                            Transactions::updateAll([
//                                'status' => Transactions::DATA_SENT,
//                            ],
//                                ['id' => $single_data->transaction_id]);

                            ReceiptData::updateAll([
                                'status' => ReceiptData::DATA_SENT,
                                'receipt_data' => $signedData,
                                'response_ack' => $resultEfd,
                                'response_status' => $responseStatus,
                            ],
                                ['id' => $single_data->id]);
                            echo 'success';
                        } else {
                            echo $resultEfd;
                            die;
                        }

                    }

                }

            }

        }
    }

}
