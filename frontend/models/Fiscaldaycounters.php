<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "fiscaldaycounters".
 *
 * @property int $id
 * @property int $eVFDID
 * @property int|null $fiscalDayNo
 * @property int $fiscalCounterType
 * @property int $fiscalCounterCurrency
 * @property int|null $fiscalCounterTaxId
 * @property int|null $fiscalCounterMoneyType
 * @property float $fiscalCounterValue
 * @property string $datetime
 */
class Fiscaldaycounters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fiscaldaycounters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['eVFDID', 'fiscalCounterType', 'fiscalCounterCurrency', 'fiscalCounterValue', 'datetime'], 'required'],
            [['eVFDID', 'fiscalDayNo', 'fiscalCounterType', 'fiscalCounterCurrency', 'fiscalCounterTaxId', 'fiscalCounterMoneyType'], 'integer'],
            [['fiscalCounterValue'], 'number'],
            [['datetime'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yii', 'ID'),
            'eVFDID' => Yii::t('yii', 'E Vfdid'),
            'fiscalDayNo' => Yii::t('yii', 'Fiscal Day No'),
            'fiscalCounterType' => Yii::t('yii', 'Fiscal Counter Type'),
            'fiscalCounterCurrency' => Yii::t('yii', 'Fiscal Counter Currency'),
            'fiscalCounterTaxId' => Yii::t('yii', 'Fiscal Counter Tax ID'),
            'fiscalCounterMoneyType' => Yii::t('yii', 'Fiscal Counter Money Type'),
            'fiscalCounterValue' => Yii::t('yii', 'Fiscal Counter Value'),
            'datetime' => Yii::t('yii', 'Datetime'),
        ];
    }
}
