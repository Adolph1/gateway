<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "tra_logs".
 *
 * @property int $id
 * @property string $message
 * @property string $module
 * @property string $action
 * @property string $status
 * @property int $znumber
 * @property string $datetime
 */
class TraLogs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tra_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message', 'module', 'action', 'status', 'znumber', 'datetime'], 'required'],
            [['message'], 'string'],
            [['znumber'], 'integer'],
            [['datetime'], 'safe'],
            [['module', 'action', 'status'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message' => 'Message',
            'module' => 'Module',
            'action' => 'Action',
            'status' => 'Status',
            'znumber' => 'Znumber',
            'datetime' => 'Datetime',
        ];
    }
}
