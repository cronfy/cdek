<?php

namespace cronfy\cdek\common\models\crud;

use Yii;

/**
 * This is the model class for table "cdek_city".
 *
 * @property integer $id
 * @property integer $city_code
 * @property string $name
 * @property string $data
 */
class CdekCity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cdek_city';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'city_code/required' => ['city_code', 'required'],
            'name/required' => ['name', 'required'],
            'city_code/integer' => ['city_code', 'integer'],
            'name/length' => ['name', 'string', 'max' => 255],
            'data/length' => ['data', 'string', 'max' => 1024],
            'city_code/unique' => [['city_code'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'city_code' => 'City Code',
            'name' => 'Name',
            'data' => 'Data',
        ];
    }
}
