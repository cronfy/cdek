<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 23.10.17
 * Time: 18:34
 */

namespace cronfy\cdek;

use cronfy\cdek\common\misc\CityRepository;
use cronfy\cdek\common\models\CdekCity;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Module;
use yii\helpers\ArrayHelper;

class BaseModule extends Module
{

    public $apiConfig;
    public $availableTariffs = [];
    public $cache;

    public function getCache() {
        if (!$this->cache) {
            $this->cache = Yii::$app->cache;
        }

        if (!is_object($this->cache) || is_callable($this->cache)) {
            $this->cache = Yii::createObject($this->cache, [$this]);
        }

        return $this->cache;
    }

    protected $_cityRepository;
    public function getCityRepository() {
        if (!$this->_cityRepository) {
            $this->_cityRepository = new CityRepository();
        }

        return $this->_cityRepository;
    }

    public function getControllerPath()
    {
        // Переопределяем, как мы будем искать контроллеры модуля, так как родной
        // метод Yii не подходит, вот почему:
        //
        // Yii определяет путь к контроллеру через алиас по controllerNamespace.
        // То есть, есть модуль cronfy/somemd, в нем есть
        // приложение console (вложенный модуль cronfy\somemd\console\Module).
        // В console есть контроллер cronfy\somemd\console\controllers\InitController.
        // Yii, при поиске контроллера для cronfy\somemd\console\Module, возьмет controllerNamespace
        // - это будет cronfy\somemd\console\Module\controllers - и приставит к нему собачку,
        // будто это алиас: @cronfy\somemd\console\Module\controllers.
        // И в том месте, куда отрезолвится алиас, будет искать классы контроллера.
        //
        // Алиас создавать не хочется, так как это лишняя сущность, которая может
        // конфликтовать с другими алиасами (мы - модуль и не знаем, какие алиасы
        // уже используются в приложении). Поэтому определим путь к контроллерам
        // своим способом.

        // ищем контроллер в папке controllers/ относительно класса Module
        $rc = new \ReflectionClass(get_class($this));
        return dirname($rc->getFileName()) . '/controllers';
    }

    public function getPvz($city_code, $pvz_code)
    {
        /** @var CdekCity $city */
        $city = CdekCity::findOne(['city_code' => $city_code]);
        $pvz = $city->getPvz($pvz_code);
        return $pvz;
    }

    protected $_api;
    public function getApi()
    {
        if (!$this->_api) {
            $config = $this->apiConfig;
            $this->_api = new Api($config);
        }

        return $this->_api;
    }

    public function calculate($cargo, $fromCity, $toCity, $params) {
        if (is_scalar($params)) {
            return $this->calculateByMode($cargo, $fromCity, $toCity, $params);
        }

        if ($params['tariff']) {
            return $this->calculateByTariff($cargo, $fromCity, $toCity, $params['tariff']);
        }

        throw new InvalidArgumentException("Wrong params");
    }

    /**
     * @param $cargo Cargo
     * @param $fromCity
     * @param $toCity
     * @param $tariff
     * @return mixed
     */
    protected function calculateByTariff($cargo, $fromCity, $toCity, $tariff)
    {
        $payload = [
            'dateExecute' => date('Y-m-d'),
            'senderCityId' => $fromCity,
            'receiverCityId' => $toCity,
            'tariffList' => [
                ['id' => $tariff, 'priority' => 1]
            ],
            'goods' => [
                [
                    'width' => $cargo->width,
                    'height' => $cargo->height,
                    'length' => $cargo->length,
                    'weight' => $cargo->weight,
                ]
            ]
        ];
        $result = $this->getApi()->calculate($payload);
        return $result;
    }

    /**
     * @param $cargo Cargo
     * @param $fromCity
     * @param $toCity
     * @param $mode
     * @return mixed
     */
    protected function calculateByMode($cargo, $fromCity, $toCity, $mode)
    {
        $availableTariffs = ArrayHelper::index($this->availableTariffs, 'id');
        $matchingTariffs = Tariffs::getMatching([
            'ids' => ArrayHelper::getColumn($availableTariffs, 'id'),
            'modes' => [$mode]
        ]);

        $tariffList = [];
        foreach ($matchingTariffs as $matchingTariff) {
            $id = $matchingTariff['id'];
            $tariffList[] = [
                'id' => $id,
                'priority' => @$availableTariffs[$id]['priority'] ?: 100,
            ];
        }

        $payload = [
            'dateExecute' => date('Y-m-d'),
            'senderCityId' => $fromCity,
            'receiverCityId' => $toCity,
//            'tariffId' => $this->tariffId,
            'tariffList' => $tariffList,
            'goods' => [
                [
                    'width' => $cargo->width,
                    'height' => $cargo->height,
                    'length' => $cargo->length,
                    'weight' => $cargo->weight,
                ]
            ]
        ];
        $result = $this->getApi()->calculate($payload);
        return $result;
    }
}
