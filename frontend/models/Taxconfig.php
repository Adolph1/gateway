<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "taxconfig".
 *
 * @property int $id
 * @property int $eVFDID
 * @property string $taxCode
 * @property string $taxName
 * @property int $taxPercent
 * @property string $taxCategoryName
 * @property string $taxCategoryCode
 * @property int $taxId
 * @property string $datetime
 */
class Taxconfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'taxconfig';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['eVFDID', 'taxCode', 'taxName', 'taxPercent', 'taxCategoryName', 'taxCategoryCode', 'taxId', 'datetime'], 'required'],
            [['eVFDID', 'taxPercent', 'taxId'], 'integer'],
            [['datetime'], 'safe'],
            [['taxCode'], 'string', 'max' => 10],
            [['taxName', 'taxCategoryName', 'taxCategoryCode'], 'string', 'max' => 100],
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
            'taxCode' => Yii::t('yii', 'Tax Code'),
            'taxName' => Yii::t('yii', 'Tax Name'),
            'taxPercent' => Yii::t('yii', 'Tax Percent'),
            'taxCategoryName' => Yii::t('yii', 'Tax Category Name'),
            'taxCategoryCode' => Yii::t('yii', 'Tax Category Code'),
            'taxId' => Yii::t('yii', 'Tax ID'),
            'datetime' => Yii::t('yii', 'Datetime'),
        ];
    }
}
