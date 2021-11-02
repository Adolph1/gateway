<?php

namespace backend\modules\webvfd\models;

use frontend\models\DailyCounter;
use frontend\models\GlobalCounter;
use SimpleXMLElement;
use Yii;


/**
 * This is the model class for table "web_vfd_api".
 *
 * @property int $id
 * @property string $request_title
 * @property string $request_name
 * @property string $url
 * @property string|null $maker
 * @property string|null $maker_time
 */
class WebVfdApi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    const REGISTRATION = 1;
    const TOKEN_ACCESS = 2;
    const ISSUE_RECEIPT = 3;
    const Z_REPORT = 4;
    const VERIFY_RECEIPT = 5;


    public static function tableName()
    {
        return 'web_vfd_api';
    }

    public static function getDailyCounterByCompanyCode($companyCode,$date)
    {
        $DCounter = DailyCounter::find()
            ->where(['company_id' => $companyCode])
            ->max('reference_no');

        $check_last = DailyCounter::find()
            ->where(['company_id' => $companyCode])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $current_date = date('Y-m-d', strtotime($date));

        if ($DCounter != '') {

            if ($current_date == $check_last->created_at) {

                $daily_counter = $check_last['reference_no'] + 1;


            } elseif ($current_date > $check_last->created_at) {

                $daily_counter = '1';
            }

        } else {

            $daily_counter = 1;
        }

        return $daily_counter;

    }

    public static function getGlobalCounterByCompanyCode($companyCode)
    {
        $model_global = GlobalCounter::find()->where(['company_id' => $companyCode])->max('reference_no');

        $check_last_number = GlobalCounter::find()->where(['company_id' => $companyCode])->orderBy(['id' => SORT_DESC])->one();

        if ($model_global != null) {

            $global_counter = $check_last_number['reference_no'] + 1;

        } else {

            $global_counter = 1;

        }

        return $global_counter;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_title', 'request_name', 'url'], 'required'],
            [['maker_time'], 'safe'],
            [['request_title', 'request_name', 'url', 'maker'], 'string', 'max' => 200],
            [['request_name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'request_title' => Yii::t('app', 'Request Title'),
            'request_name' => Yii::t('app', 'Request Name'),
            'url' => Yii::t('app', 'Url'),
            'maker' => Yii::t('app', 'Maker'),
            'maker_time' => Yii::t('app', 'Maker Time'),
        ];
    }


    public static function getPrivateKey($id)
{
    $companyDetails = Company::getCompanyDetailsById($id);
    $cert_info = array();
    $cert_store = file_get_contents("uploads/file/$companyDetails->file");
    $isGenerated = openssl_pkcs12_read($cert_store, $cert_info, $companyDetails->certificate_password);
    if (!$isGenerated) {
        throw new \RuntimeException(('Invalid Password'));
    }
    $public_key = $cert_info['cert'];
    $private_key = $cert_info['pkey'];
    $pkeyid = openssl_get_privatekey($private_key);
    if (!$pkeyid) {
        throw new \RuntimeException(('Invalid private key'));
    }else{
        return $pkeyid;
    }

}

    public static function getApiToken($id)
    {

        $companyDetails = Company::getCompanyDetailsById($id);
        $url = UrlConfig::getUrlByName(WebVfdApi::TOKEN_ACCESS);
        $username = $companyDetails->company_username;
        $password = $companyDetails->password;
        $urlToken = $url->url;

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

            return $access_token;

        } catch (\Exception $e) {
            //CALLING OFFLINE BLOCK

          return null;
        }

    }

    public static function companyRegistration($id)
    {

        $company = Company::getCompanyDetailsById($id);
        $url = UrlConfig::getUrlByName(WebVfdApi::REGISTRATION);


            $companySerialNumber = $company->certificate_serial;
            $TIN = $company->tin;
            $SERIAL = $company->serial_number;
            $certificate_password = $company->certificate_password;

            try {

                $cert_store = file_get_contents("uploads/file/$company->file");

            } catch (\Exception $e) {
                //PFX file is invalid
                $error = 405;


                return $error;

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


            function arrayToXml1($array, $rootElement = null, $xml = null)
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

            $xml = arrayToXml1($arrayReceipt);

            $xml_converted = trim(str_replace('<?xml version="1.0"?>', '', $xml));


            $cert_info = array();

            $isGenerated = openssl_pkcs12_read($cert_store, $cert_info, $certificate_password);

            if (!$isGenerated) {

               //Invalid password
                $error = 406;
                return $error;
            }

            $public_key = $cert_info['cert'];

            $private_key = $cert_info['pkey'];

            $pkeyid = openssl_get_privatekey($private_key);

            //  return $pkeyid;
            if (!$pkeyid) {
                // throw new \RuntimeException(('Invalid private key'));
                $error = 407;
                return $error;
            }

            $isGenerated2 = openssl_sign($xml_converted, $signature, $pkeyid, OPENSSL_ALGO_SHA1);

            openssl_free_key($pkeyid);
            if (!$isGenerated2) {
                //   throw new \RuntimeException(('Computing of the signature failed'));

               $error = 408;
                return $error;
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


            $curl = curl_init($url->url);
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
                //Already registered in EFDMS

             $error = 409;
                return $error;

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

                  $error = 000;
                    return $error;

                }else{
                    //Failed to register to EFDMS
                    $error = 500;
                    return $error;

                }


        }
    }

    public static function issueReceipt($access_token,$signedData,$id)
    {
        $company = Company::getCompanyDetailsById($id);
//        $urlReceipt = 'http://18.217.105.68:8080/api/vfdRctPost';
        $urlReceipt = UrlConfig::getUrlByName(WebVfdApi::ISSUE_RECEIPT);
        $urlReceipt = $urlReceipt->url;
        $routing_key = 'vfdrct';
        $headers = array(
            'Content-type: Application/xml',
            'Routing-Key: ' . $routing_key,
            'Cert-Serial: ' . base64_encode($company->certificate_serial),
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



        return $resultEfd;


    }

    public static function convertToXml($array, $rootElement = null, $xml = null,$rootTag)
    {

            $_xml = $xml;
            // If there is no Root Element then insert root
            if ($_xml === null) {
                $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : $rootTag);
            }

            // Visit all key value pair
            foreach ($array as $k => $v) {

                // If there is nested array then
                if (is_array($v)) {

                    // Call function for nested array
                    WebVfdApi::convertToXml($v, $k, $_xml->addChild($k),$rootTag=null);
                } else {

                    // Simply add child element.
                    $_xml->addChild($k, $v);
                }



        }
        return $_xml->asXML();

//        foreach ($array as $k => $v) {
//            if(is_array($v)) {
//                (is_int($k)) ? WebVfdApi::convertToXml($v, $xml->addChild($child_name), $v) : WebVfdApi::convertToXml($v, $xml->addChild($k), $child_name);
//            } else {
//                (is_int($k)) ? $xml->addChild($child_name, $v) : $xml->addChild($k, $v);
//            }
//        }
////        $xml =  str_replace('"', '', $xml->asXML());
//
//        return $xml->asXML();

    }


}
