<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%web_vfd_api}}`.
 */
class m210706_073128_create_web_vfd_api_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%web_vfd_api}}', [
            'id' => $this->primaryKey(),
            'request_title' => $this->string(200)->notNull(),
            'request_name' => $this->string(200)->notNull()->unique(),
            'url' => $this->string(200)->notNull(),
            'maker' => $this->string(200),
            'maker_time' => $this->dateTime()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%web_vfd_api}}');
    }
}
