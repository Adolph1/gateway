<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "grade".
 *
 * @property int $id
 * @property string $name
 * @property float|null $price
 * @property float|null $temp_coeficient
 * @property string|null $maker
 * @property string|null $maker_time
 *
 * @property Pump $pump
 */
class Grade extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'grade';
    }

    public static function getAll()
    {
        return ArrayHelper::map(Grade::find()->all(), 'id', 'name');
    }

    public static function getNameById($gradeId)
    {
        $grade = Grade::findOne($gradeId);
        if ($grade != null) {
            return $grade->name;
        } else {
            return null;
        }
    }

    public static function geCoefficientById($gradeId)
    {
        $grade = Grade::findOne($gradeId);
        if ($grade != null) {
            return $grade->temp_coeficient;
        } else {
            return null;
        }
    }

    public static function getPriceById($gradeid)
    {
        $grade = Grade::findOne($gradeid);
        if ($grade != null) {
            return $grade->price;
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['price', 'temp_coeficient'], 'number'],
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
            'price' => 'Price',
            'temp_coeficient' => 'Temp Coefficient',
            'maker' => 'Maker',
            'maker_time' => 'Maker Time',
        ];
    }

}
