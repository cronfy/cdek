<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cdek_city_to_pvz`.
 */
class m000000_000002_cronfy_cdek_create_cdek_city_to_pvz_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('cdek_city_to_pvz', [
            'id' => $this->primaryKey(),
            'city_id' => $this->integer()->unsigned(),
            'pvz_id' => $this->integer()->unsigned(),
        ]);

        $this->createIndex('city_id,pvz_id', 'cdek_city_to_pvz', ['city_id', 'pvz_id'], true);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('cdek_city_to_pvz');
    }
}
