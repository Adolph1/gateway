<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "signed_unsigned_z_report".
 *
 * @property int $id
 * @property string $znumber
 * @property string $data
 * @property string|null $type
 * @property string|null $ref_no
 * @property string $created_at
 * @property string $ackmsg
 * @property int $created_by
 * @property int $status
 */
class SignedUnsignedZReport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'signed_unsigned_z_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['znumber', 'data', 'created_at', 'created_by', 'status'], 'required'],
            [['data'], 'string'],
            [['created_at'], 'safe'],
            [['created_by', 'status'], 'integer'],
            [['znumber'], 'string', 'max' => 200],
            [['type'], 'string', 'max' => 20],
            [['ackmsg'], 'string'],
            [['ref_no',''], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'znumber' => 'Znumber',
            'data' => 'Data',
            'type' => 'Type',
            'ref_no' => 'Ref No',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'status' => 'Status',
        ];
    }
}
