<?php

namespace cronfy\cdek\common\models\crud;

use Yii;

/**
 * This is the model class for table "cdek_pvz".
 *
 * @property integer $id
 * @property string $code
 * @property double $lat
 * @property double $lng
 * @property string $name
 * @property string $type
 * @property string $data
 */
class CdekPvz extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cdek_pvz';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'code/required' => ['code', 'required'],
            'lat/required' => ['lat', 'required'],
            'lng/required' => ['lng', 'required'],
            'name/required' => ['name', 'required'],
            'lat/number' => ['lat', 'number'],
            'lng/number' => ['lng', 'number'],
            'code/length' => ['code', 'string', 'max' => 255],
            'name/length' => ['name', 'string', 'max' => 255],
            'type/length' => ['type', 'string', 'max' => 255],
            'data/length' => ['data', 'string', 'max' => 2048],
            'code/unique' => [['code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'lat' => 'Lat',
            'lng' => 'Lng',
            'name' => 'Name',
            'type' => 'Type',
            'data' => 'Data',
        ];
    }
}
