<?php

namespace backend\modules\webvfd\models;

use Yii;

/**
 * This is the model class for table "url_config".
 *
 * @property int $id
 * @property string $name
 * @property string $prefix_url
 * @property string $post_fix
 * @property string $domain_name
 * @property string|null $url
 * @property string|null $created_at
 * @property int $created_by
 */
class UrlConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'url_config';
    }

    public static function getUrlByName($id)
    {
        $url = UrlConfig::findOne(['request_id' => $id]);
        if($url != null){
            return $url;
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
            [['name', 'prefix_url', 'post_fix', 'domain_name', 'created_by'], 'required'],
            [['created_at'], 'safe'],
            [['created_by'], 'integer'],
            [['name', 'post_fix', 'url'], 'string', 'max' => 200],
            [['prefix_url', 'domain_name'], 'string', 'max' => 30],
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
            'prefix_url' => 'Prefix Url',
            'post_fix' => 'Post Fix',
            'domain_name' => 'Domain Name',
            'url' => 'Url',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
