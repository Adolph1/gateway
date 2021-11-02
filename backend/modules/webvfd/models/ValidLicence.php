<?php

namespace backend\modules\webvfd\models;

use Yii;

/**
 * This is the model class for table "valid_licence".
 *
 * @property int $id
 * @property int $company_id
 * @property string $activation_date
 * @property string $expired_date
 * @property string $created_at
 * @property int $created_by
 * @property int $status
 */
class ValidLicence extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'valid_licence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'activation_date', 'expired_date', 'created_at', 'created_by'], 'required'],
            [['company_id', 'created_by'], 'integer'],
            [['activation_date', 'expired_date', 'created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company',
            'activation_date' => 'Activation Date',
            'expired_date' => 'Expired Date',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
