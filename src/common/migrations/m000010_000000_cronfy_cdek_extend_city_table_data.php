<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cdek_city`.
 */
class m000010_000000_cronfy_cdek_extend_city_table_data extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->alterColumn('cdek_city', 'data', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return false;
    }
}
