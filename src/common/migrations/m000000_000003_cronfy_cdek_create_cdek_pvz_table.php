<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cdek_pvz`.
 */
class m000000_000003_cronfy_cdek_create_cdek_pvz_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('cdek_pvz', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'lat' => $this->double()->notNull(),
            'lng' => $this->double()->notNull(),
            'name' => $this->string()->notNull(),
            'type' => $this->string(),
            'data' => $this->string(2048),
        ], 'CHARACTER SET utf8 ENGINE=InnoDb');

        $this->addColumn('cdek_pvz', 'created_at', $this->integer()->notNull());
        $this->addColumn('cdek_pvz', 'updated_at', $this->integer()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('cdek_pvz');
    }
}
