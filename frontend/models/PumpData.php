<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "pump_data".
 *
 * @property int $id
 * @property string|null $pump_data
 * @property string|null $transID
 * @property int|null $transaction
 * @property int|null $status
 * @property string|null $date_time
 */
class PumpData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pump_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pump_data'], 'string'],
            [['transaction', 'status'], 'integer'],
            [['date_time'], 'safe'],
            [['transID'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pump_data' => Yii::t('app', 'Pump Data'),
            'transID' => Yii::t('app', 'Trans ID'),
            'transaction' => Yii::t('app', 'Transaction'),
            'status' => Yii::t('app', 'Status'),
            'date_time' => Yii::t('app', 'Date Time'),
        ];
    }
}
