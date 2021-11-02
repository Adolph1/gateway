<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "nozzel".
 *
 * @property int $id
 * @property string $name
 * @property int $pump_id
 * @property int $grade_id
 * @property string|null $maker
 * @property string|null $maker_time
 *
 * @property Pump $pump
 * @property Grade $grade
 */
class Nozzel extends \yii\db\ActiveRecord
{
    const VOLUME = 1;
    const AMOUNT = 2;
    const FULL_TANK = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nozzel';
    }

    public static function getNozzleByPumpId($id)
    {
        $nozzles = Nozzel::findAll(['pump_id' => $id]);
        if($nozzles != null){

            return $nozzles;
        }else{
            return null;
        }
    }

    public static function getArrayPresetTypes()
    {
        return [
           'VOLUME' => self::VOLUME,
           'AMOUNT' => self::AMOUNT,
           'FULL TANK' => self::FULL_TANK
        ];
    }

    public static function getAllByPumpId($pump_id)
    {
        return ArrayHelper::map(Nozzel::find()->where(['pump_id' => $pump_id])->all(),'id','name');
    }

    public static function getGradeIdByNozzelNo($nozzel_no)
    {
        $nozzles = Nozzel::findOne(['id' => $nozzel_no]);
        if($nozzles != null){

            return $nozzles->grade_id;
        }else{
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'pump_id', 'grade_id'], 'required'],
            [['pump_id', 'grade_id'], 'integer'],
            [['maker_time'], 'safe'],
            [['name', 'maker'], 'string', 'max' => 200],
            [['pump_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pump::className(), 'targetAttribute' => ['pump_id' => 'id']],
            [['grade_id'], 'exist', 'skipOnError' => true, 'targetClass' => Grade::className(), 'targetAttribute' => ['grade_id' => 'id']],
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
            'pump_id' => 'Pump ',
            'grade_id' => 'Grade ',
            'maker' => 'Maker',
            'maker_time' => 'Maker Time',
        ];
    }

    /**
     * Gets query for [[Pump]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPump()
    {
        return $this->hasOne(Pump::className(), ['id' => 'pump_id']);
    }

    /**
     * Gets query for [[Grade]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrade()
    {
        return $this->hasOne(Grade::className(), ['id' => 'grade_id']);
    }
}
