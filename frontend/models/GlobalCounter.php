<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "global_counter".
 *
 * @property int $id
 * @property int $reference_no
 * @property int $company_id
 * @property int $eVFDID
 * @property string $created_at
 * @property int $created_by
 */
class GlobalCounter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'global_counter';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reference_no', 'company_id', 'created_at', 'created_by'], 'required'],
            [['reference_no', 'company_id', 'created_by','eVFDID'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reference_no' => 'Reference No',
            'company_id' => 'Company ID',
            'eVFDID' => 'eVFDID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
