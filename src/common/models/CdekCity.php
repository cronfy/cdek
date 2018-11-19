<?php

namespace cronfy\cdek\common\models;

use cronfy\cdek\BaseModule;
use paulzi\jsonBehavior\JsonBehavior;
use Yii;

/**
 * @property CdekPvz[] $pvzs
 */
class CdekCity extends crud\CdekCity
{
    /**
     * Это ОЧЕНЬ плохо, потому что мы хардкодим имя модуля.
     * @return BaseModule
     */
    public function getModule() {
        return Yii::$app->getModule('cdek');
    }

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
        $cache = $this->getModule()->getCache();
        $cityCode = $this->city_code;
        $url = "https://integration.cdek.ru/pvzlist.php?cityid=$cityCode&type=ALL";
        $xmlstring = $cache->getOrSet("cronfy.cdek.url.$url", function () use ($url) {
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

    /**
     * @return CdekCityDTO
     */
    public function getDTO() {
        $dto = new CdekCityDTO();
        foreach (get_object_vars($dto) as $key => $value) {
            $dto->$key = $this->data[$key];
        }
        return $dto;
    }

}
