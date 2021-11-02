<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "fiscaldaynumber".
 *
 * @property int $id
 * @property int $eVFDID
 * @property string|null $opened_date
 * @property string|null $closed_date
 * @property int $status
 */
class Fiscaldaynumber extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fiscaldaynumber';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['eVFDID', 'status'], 'required'],
            [['eVFDID', 'status'], 'integer'],
            [['opened_date', 'closed_date'], 'safe'],
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
            'opened_date' => Yii::t('yii', 'Opened Date'),
            'closed_date' => Yii::t('yii', 'Closed Date'),
            'status' => Yii::t('yii', 'Status'),
        ];
    }
}
