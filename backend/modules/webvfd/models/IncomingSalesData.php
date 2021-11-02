<?php

namespace backend\modules\webvfd\models;

use Yii;

/**
 * This is the model class for table "incoming_sales_data".
 *
 * @property int $id
 * @property string $sales_data
 * @property string|null $qrCode
 * @property int $company_id
 * @property int $status
 * @property string $date_time
 *
 *  @property Company $myCompany
 */


class IncomingSalesData extends \yii\db\ActiveRecord
{
    const DATA_RECEIVED = 0;
    const DATA_SENT = 1;
    const REJECTED = 2;

    private $_statusLabel;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'incoming_sales_data';
    }


    public function getStatusLabel()
    {
        if ($this->_statusLabel === null) {
            $statuses = self::getArrayStatus();
            $this->_statusLabel = $statuses[$this->status];
        }
        return $this->_statusLabel;
    }

    /**
     * @inheritdoc
     */
    public static function getArrayStatus()
    {
        return [
            self::DATA_RECEIVED => Yii::t('app', 'RECEIVED'),
            self::DATA_SENT => Yii::t('app', 'SENT'),
            self::REJECTED => Yii::t('app', 'REJECTED'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sales_data', 'status', 'date_time'], 'required'],
            [['sales_data'], 'string'],
            [['status','company_id'], 'integer'],
            [['date_time'], 'safe'],
            [['qrCode'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sales_data' => Yii::t('app', 'Sales Data'),
            'qrCode' => Yii::t('app', 'Qr Code'),
            'company_id' => Yii::t('app', 'Company'),
            'status' => Yii::t('app', 'Status'),
            'date_time' => Yii::t('app', 'Date Time'),
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMyCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
