<?php

namespace cronfy\cdek\common\models;

use paulzi\jsonBehavior\JsonBehavior;
use Yii;

/**
 * @property CdekPvz[] $pvzs
 */
class CdekCity extends crud\CdekCity
{

    public function behaviors()
    {
        $behaviors = [
            [
                'class' => JsonBehavior::class,
                'attributes' => ['data'],
            ],
        ];

        return $behaviors;
    }

    public function rules()
    {
        $rules = parent::rules();
        unset ($rules['data/length']); // это json field. отключаем, чтобы при validate() не ругался
        return $rules;
    }

    public function getPvz($code)
    {
        $pvzs = $this->getPvzs();
        return $pvzs[$code];
    }

    /**
     * @return CdekPvz[]
     */
    public function getPvzs()
    {
        $cityCode = $this->city_code;
        $url = "http://gw.edostavka.ru:11443/pvzlist.php?cityid=$cityCode&type=ALL";
        $xmlstring = Yii::$app->cache->getOrSet("cronfy.cdek.url.$url", function () use ($url) {
            return file_get_contents($url);
        }, 60 * 60 * 24 * 7);

        $data = new \SimpleXMLElement($xmlstring);

        $all = [];
        foreach ($data as $pvz) {
            $pvz = current($pvz->attributes()); // to array
            array_walk_recursive($pvz, function (&$v) {
                $v = trim($v);
            });

            $cdekPvz = new CdekPvz();

            $cdekPvz->setAttributes([
                'code' => $pvz['Code'],
                'name' => $pvz['Name'],
                'lat' => (float) $pvz['coordY'],
                'lng' => (float) $pvz['coordX'],
                'type' => $pvz['Type'],
            ]);
            $cdekPvz->city_code = $cityCode;
            $cdekPvz->data->set($pvz);

            $all[$cdekPvz->code] = $cdekPvz;
        }

        return $all;
    }
}
