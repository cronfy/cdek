<?php

namespace cronfy\cdek\common\models;

use paulzi\jsonBehavior\JsonBehavior;
use paulzi\jsonBehavior\JsonField;

/**
 * Раньше это была ActivRecord, но больше мы не храним модель в БД. Позже отрефакторим
 * на обычную модель.
 * @property JsonField $data
 */
class CdekPvz extends crud\CdekPvz
{

    public $city_code;

    public function behaviors()
    {
        return [
            [
                'class' => JsonBehavior::class,
                'attributes' => ['data'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function isAttributeChanged($name, $identical = true)
    {
        if ($this->$name instanceof JsonField) {
            return (string)$this->$name !== $this->getOldAttribute($name);
        } else {
            return parent::isAttributeChanged($name, $identical);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDirtyAttributes($names = null)
    {
        $result = [];
        $data = parent::getDirtyAttributes($names);
        foreach ($data as $name => $value) {
            if ($value instanceof JsonField) {
                if ((string)$value !== $this->getOldAttribute($name)) {
                    $result[$name] = $value;
                }
            } else {
                $result[$name] = $value;
            }
        }
        return $result;
    }
}
