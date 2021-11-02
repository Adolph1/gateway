<?php

namespace backend\modules\webvfd\models;

use common\models\User;
use backend\modules\webvfd\models\Url;
use ruturajmaniyar\mod\audit\behaviors\AuditEntryBehaviors;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $name
 * @property int $tin
 * @property string|null $vrn
 * @property string $serial_number
 * @property string $street
 * @property string $certificate_password
 * @property string $city
 * @property string $country
 * @property string $uin
 * @property string $status
 * @property string $company_username
 * @property string $password
 * @property string $certififcate_serial
 * @property string $tax_office
 * @property string|null $address
 * @property string|null $email
 * @property string|null $business_licence
 * @property string|null $contact_person
 * @property int $company_id_type
 * @property int $file
 * @property int $reg_status
 * @property string $create_by
 * @property string $created_at
 * @property string|null $updated_at
 */
class Company extends \yii\db\ActiveRecord
{

    const ACTIVE = 1;
    const IN_ACTIVE = 0;

    const ALREADY_REGISTERED = 1;
    const NOT_REGISTERED = 0;

    public $pfx_file;

    private $_statusLabel;

    public static function getCompanyDetailsById($id)
    {

        $company = Company::findOne($id);
        if($company != null){
            return $company;
        }else{
            return null;
        }
    }

    public function behaviors()
    {
        return [
            'auditEntryBehaviors' => [
                'class' => AuditEntryBehaviors::class
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tin', 'serial_number', 'certificate_password', 'pfx_file', 'certificate_serial', 'company_id_type', 'create_by', 'created_at', 'status'], 'required', 'on' => 'create'],
            [['tin', 'company_id_type', 'status', 'reg_status'], 'integer'],
            [['email',], 'email'],
            [['tin', 'vrn', 'serial_number', 'uin'], 'unique', 'message' => 'Number exist'],
            ['tin', 'match', 'pattern' => '/^\d{9}$/', 'message' => 'Field must contain exactly 9 digits.'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'vrn', 'serial_number', 'uin', 'tax_office', 'address', 'email', 'business_licence', 'contact_person', 'create_by', 'file', 'company_username', 'certificate_serial', 'password','street','city','country'], 'string', 'max' => 255],

            [['pfx_file',], 'file', 'extensions' => 'pfx', 'skipOnEmpty' => true,
                'checkExtensionByMimeType' => false,],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company_username' => 'Company Username',
            'password' => 'Password',
            'certificate_serial' => 'Company Serial no',
            'tin' => 'Tin',
            'vrn' => 'Vrn',
            'status' => 'Company status',
            'reg_status' => 'TRA Registration status',
            'serial_number' => 'Certificate Key',
            'uin' => 'Uin',
            'tax_office' => 'Tax Office',
            'address' => 'Address',
            'street' => 'Street',
            'city' => 'City',
            'country' => 'Country',
            'email' => 'Email',
            'receipt_number' => 'Receipt Code',
            'business_licence' => 'Business Licence',
            'contact_person' => 'Contact Person',
            'company_id_type' => 'Company Type',
            'create_by' => 'Create By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    public function getCompanyType()
    {
        return $this->hasOne(CompanyType::className(), ['id' => 'company_id_type']);
    }

    /**
     * @inheritdoc
     */
    public static function getCompanyStatus()
    {
        return [
            self::ACTIVE => Yii::t('app', 'ACTIVE'),
            self::IN_ACTIVE => Yii::t('app', 'INACTIVE'),
        ];
    }
    public function getStatusLabel()
    {
        if ($this->_statusLabel === null) {
            $statuses = self::getCompanyStatus();
            $this->_statusLabel = $statuses[$this->status];
        }
        return $this->_statusLabel;
    }

    public function getUser0()
    {
        return $this->hasOne(User::className(), ['id' => 'create_by']);

    }


    public static function getCompanyList()
    {
        return ArrayHelper::map(Company::find()
            ->where(['status' => Company::ACTIVE])
            ->all(), 'id', 'name');

    }

    public static function getStatus()
    {
        return ArrayHelper::map(Status::find()
            ->all(), 'id', 'name');

    }

    public static function getTotalAllCompanies()
    {
        $applications=Company::find()
            ->count();

        if ($applications > 0) {
            echo $applications;
        } else {
            echo 0;
        }

    }

    public static function getTotalTRARegisteredCompanies()
    {
        $applications=Company::find()
            //  ->where(['status'=>1])
            ->where(['reg_status' => Company::ALREADY_REGISTERED])
            ->count();
        if ($applications > 0) {
            echo $applications;
        } else {
            echo 0;
        }

    }

    public static function getTotalActiveCompanies()
    {
        $applications=Company::find()
            ->where(['status'=>Company::ACTIVE])
            ->count();
        if ($applications > 0) {
            echo $applications;
        } else {
            echo 0;
        }

    }

    public static function getTotalInActiveCompanies()
    {
        $in_active = Status::find()->where(['name'=>"IN ACTIVE"])->one();

        $applications=Company::find()
            ->where(['status'=>$in_active->id])
            ->count();
        if ($applications > 0) {
            echo $applications;
        } else {
            echo 0;
        }

    }

    public static function getTotalNotRegisteredCompanies()
    {
        $applications=Company::find()
            //  ->where(['status'=>Company::ACTIVE])
            ->andWhere(['reg_status' => Company::NOT_REGISTERED])
            ->count();
        if ($applications > 0) {
            echo $applications;
        } else {
            echo 0;
        }

    }

    public static function checkDataFromTRA()
    {
        //URL CONFIGURATION FROM BACKEND
        $url = UrlConfig::find()->where(['name'=>'REGISTRATION'])->one();
        $domainName = $url['domain_name'];
        $hostURL = $url['url'];

        if(checkdnsrr($domainName,"ANY")){

            $companies = Company::find()
                ->where(['status' => Company::ACTIVE])
                ->orWhere(['reg_status' => Company::NOT_REGISTERED])
                ->orWhere(['vrn' => null])
                ->all();

            foreach ($companies as $key => $company) {

                if (!empty($company)) {

                    $companySerialNumber = $company->certificate_serial;
                    $TIN = $company->tin;
                    $CERTKEY = $company->serial_number;
                    $certificate_password = $company->certificate_password;
                    $cert_store = file_get_contents("file/$company->file");

                    $encodedSerial = base64_encode($companySerialNumber);
                    $headers = array(
                        'Content-type: Application/xml',
                        'Cert-Serial: ' . $encodedSerial,
                        'Client: webapi ',
                    );

                    $arrayReceipt = array(
                        'TIN' => $TIN,
                        'CERTKEY' => $CERTKEY,
                    );

                    function arrayToXml($array, $rootElement = null, $xml = null)
                    {
                        $_xml = $xml;
                        // If there is no Root Element then insert root
                        if ($_xml === null) {
                            $_xml = new \SimpleXMLElement($rootElement !== null ? $rootElement : '<REGDATA/>');
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

                    }
                    $public_key = $cert_info['cert'];

                    $private_key = $cert_info['pkey'];

                    $pkeyid = openssl_get_privatekey($private_key);

                    if (!$pkeyid) {
                        // throw new \RuntimeException(('Invalid private key'));

                    }
                    $isGenerated2 = openssl_sign($xml_converted, $signature, $pkeyid, OPENSSL_ALGO_SHA1);

                    openssl_free_key($pkeyid);
                    if (!$isGenerated2) {
                        //   throw new \RuntimeException(('Computing of the signature failed'));

                    }
                    $signatureGenerated = base64_encode($signature);

                    $data = "<?xml version='1.0' encoding='UTF-8'?><EFDMS>
                    <REGDATA>
                      <TIN>$TIN</TIN>
                      <CERTKEY>$CERTKEY</CERTKEY>
                    </REGDATA>
                    <EFDMSSIGNATURE>$signatureGenerated</EFDMSSIGNATURE>
                </EFDMS>";

                    $url = $hostURL;
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

                    curl_close($curl);

                    // $efdms_response = XmlTo::convert($resultEfd, $outputRoot = false);
                    $data = new \SimpleXMLElement($resultEfd);

                    $companyName = (string)$data->EFDMSRESP[0]->NAME;
                    $tin = (string)$data->EFDMSRESP[0]->TIN;
                    $vrn = (string)$data->EFDMSRESP[0]->VRN;
                    $uin = (string)$data->EFDMSRESP[0]->UIN;
                    $receiptCode = (string)$data->EFDMSRESP[0]->RECEIPTCODE;
                    $registrationID = (string)$data->EFDMSRESP[0]->REGID;
                    $taxOffice = (string)$data->EFDMSRESP[0]->TAXOFFICE;
                    $address = (string)$data->EFDMSRESP[0]->ADDRESS;
                    $contactPerson = (string)$data->EFDMSRESP[0]->MOBILE;
                    $username = (string)$data->EFDMSRESP[0]->USERNAME;
                    $password = (string)$data->EFDMSRESP[0]->PASSWORD;
                    $response_status = (string)$data->EFDMSRESP[0]->ACKMSG;

                    if ($response_status == "Registration Successful" || empty($vrn)) {

                        Company::updateAll([
                            'name' => $companyName,
                            'vrn' => $vrn,
                            'receipt_number' => $receiptCode,
                            'registration_id' => $registrationID,
                            'uin' => $uin,
                            'tax_office' => $taxOffice,
                            'address' => $address,
                            'contact_person' => $contactPerson,
                            'company_username' => $username,
                            'password' => $password,
                            'reg_status' => Company::ALREADY_REGISTERED,
                        ],
                            ['id' => $company->id]);

                    }

                }

            }

        }

    }


}
