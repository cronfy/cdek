<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cdek_city`.
 */
class m000000_000001_cronfy_cdek_create_cdek_city_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('cdek_city', [
            'id' => $this->primaryKey(),
            'city_code' => $this->integer()->notNull()->unique()->unsigned(),
            'name' => $this->string()->notNull(),
            'data' => $this->string(1024),
        ], 'CHARACTER SET utf8 ENGINE=InnoDb');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('cdek_city');
    }
}
