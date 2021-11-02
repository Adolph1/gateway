<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "fiscalcountertype".
 *
 * @property int $id
 * @property string $name
 * @property int $type
 */
class Fiscalcountertype extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fiscalcountertype';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yii', 'ID'),
            'name' => Yii::t('yii', 'Name'),
            'type' => Yii::t('yii', 'Type'),
        ];
    }
}
