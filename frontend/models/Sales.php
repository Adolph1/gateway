<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "sales".
 *
 * @property int $id
 * @property string|null $trn_dt
 * @property string|null $date
 * @property int|null $session_id
 * @property string|null $product
 * @property float|null $volume
 * @property float|null $price
 * @property float|null $sub_total
 * @property float|null $tax
 * @property float|null $total
 * @property int|null $pump_no
 * @property int|null $nozzel_no
 * @property int|null $pts_transaction_no
 * @property string|null $currency
 * @property string|null $qr_code
 * @property string|null $signature
 * @property int|null $status
 */
class Sales extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    const PENDING = 0;
    const UPLOADED = 1;


    public $date1;
    public $date2;

    public static function tableName()
    {
        return 'sales';
    }

    public static function getMonthlySalesByProductId($id)
    {
        $sales = array();

        for ($i=01; $i<=12; $i++){
            $i = sprintf("%02d",$i);
            $sales[] = (float)Sales::find()->where(['product_id' => $id])->andWhere(['between','trn_dt', date('Y-'.$i.'-01'), date('Y-'.$i.'-31')])->sum('total');

        }
        return $sales;

    }

    public static function getTodayTotalSalesByGradeId($Id)
    {
        $sales = (float)Sales::find()->where(['product_id' => $Id])->andWhere(['date' => date('Y-m-d')])->sum('total');
        if($sales != null){
            return $sales;
        }else{
            return 0.00;
        }
    }

    public static function getTodayTotalSoldVolumeByGradeId($Id)
    {
        $sales = (float)Sales::find()->where(['product_id' => $Id])->andWhere(['date' => date('Y-m-d')])->sum('volume');
        if($sales != null){
            return $sales;
        }else{
            return 0.00;
        }
    }

    public static function uploadSales()
    {
        $sales = Sales::find()->where(['status' => Sales::PENDING])->all();
        if($sales != null){

            try {

                // return Json
                $username = 'admin';
                $password = 'admin';
                $url = "http://localhost/psms-online/index.php?r=sales";
                $headers = array(
                    "Content-Type: application/json; charset=utf-8",
                    "Accept: application/json",
                    //"Authorization: Basic " . base64_encode($username . ":" . $password),
                );
                $datastring = json_encode($sales);

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
        else{
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['trn_dt','date1','date2','date'], 'safe'],
            [['session_id', 'pump_no', 'nozzel_no', 'pts_transaction_no', 'status'], 'integer'],
            [['volume', 'price', 'sub_total', 'total','tax'], 'number'],
            [['qr_code'], 'string'],
            [['product'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'trn_dt' => Yii::t('app', 'Transaction Date'),
            'date' => Yii::t('app', 'Date'),
            'session_id' => Yii::t('app', 'Session ID'),
            'product' => Yii::t('app', 'Product'),
            'volume' => Yii::t('app', 'Volume'),
            'price' => Yii::t('app', 'Price'),
            'sub_total' => Yii::t('app', 'Sub Total'),
            'tax' => Yii::t('app', 'Tax'),
            'total' => Yii::t('app', 'Total'),
            'pump_no' => Yii::t('app', 'Pump No'),
            'nozzel_no' => Yii::t('app', 'Nozzel No'),
            'pts_transaction_no' => Yii::t('app', 'Pts Transaction No'),
            'currency' => Yii::t('app', 'Currency'),
            'qr_code' => Yii::t('app', 'Qr Code'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    public static function getMonthlyPayments()
    {
        $sales = array();

            for ($i=01; $i<=12; $i++){

                $sales[] = (int)Sales::find()->andWhere(['between','trn_dt', date('Y-'.$i.'-01'), date('Y-'.$i.'-31')])->sum('total');

            }
            return $sales;


    }
}
