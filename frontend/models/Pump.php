<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "pump".
 *
 * @property int $id
 * @property string|null $name
 * @property int $pump_number
 * @property int $pump_address
 * @property string|null $maker
 * @property string|null $maker_time
 */
class Pump extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pump';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pump_number', 'pump_address'], 'required'],
            [['pump_number', 'pump_address'], 'integer'],
            [['maker_time'], 'safe'],
            [['name', 'maker'], 'string', 'max' => 200],
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
            'pump_number' => 'Pump Number',
            'pump_address' => 'Pump Address',
            'maker' => 'Maker',
            'maker_time' => 'Maker Time',
        ];
    }

    public static function getAll()
    {
        $pumps = ArrayHelper::map(Pump::find()->all(),'id','name');

        return $pumps;
    }


}
