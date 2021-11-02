<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "z_report_data".
 *
 * @property int $id
 * @property int $company_id
 * @property string $fiscal_code
 * @property int $znumber
 * @property string $vatrate
 * @property float $nettamount
 * @property float $discount
 * @property float $taxamount
 * @property string $pmttype
 * @property float $pmtamount
 * @property string $datetime
 * @property int $status
 * @property string|null $ackmsg
 */
class ZReportData extends \yii\db\ActiveRecord
{
    const DATA_NOT_SENT = 2;
    const DATA_SENT = 1;
    const CREATED_DATA = 0;

    public $date1;
    public $date2;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'z_report_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'fiscal_code', 'znumber', 'vatrate', 'pmttype', 'datetime'], 'required'],
            [['company_id', 'znumber', 'status'], 'integer'],
            [['nettamount', 'discount', 'taxamount', 'pmtamount'], 'number'],
            [['datetime','date1', 'date2'], 'safe'],
            [['ackmsg'], 'string'],
            [['fiscal_code'], 'string', 'max' => 50],
            [['vatrate', 'pmttype'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'fiscal_code' => 'Fiscal Code',
            'znumber' => 'Znumber',
            'vatrate' => 'Vatrate',
            'nettamount' => 'Nettamount',
            'discount' => 'Discount',
            'taxamount' => 'Taxamount',
            'pmttype' => 'Pmttype',
            'pmtamount' => 'Pmtamount',
            'datetime' => 'Datetime',
            'status' => 'Status',
            'ackmsg' => 'Ackmsg',
        ];
    }
}
