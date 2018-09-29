<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 13.08.18
 * Time: 17:28
 */

namespace cronfy\cdek\common\misc;


use cronfy\cdek\common\models\CdekCity;
use cronfy\env\Env;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class CityRepository extends BaseObject
{
    protected $_dadataClient;
    protected function getDadataClient() {
        if (!$this->_dadataClient) {
            $this->_dadataClient = new \Dadata\Client(new \GuzzleHttp\Client(), [
                'token' => Env::get('DADATA_TOKEN'),
                'secret' => Env::get('DADATA_SECRET'),
            ]);
        }

        return $this->_dadataClient;
    }

    protected function isMatchesByFiasLatLng($fias, $lat, $lng) {
        $cacheKey = 'cdek.cityRepository.dadata.fias.' . $fias;
//        Yii::$app->cache->delete($cacheKey);
        $data = Yii::$app->cache->getOrSet(
            $cacheKey,
            function () use ($fias) {
                $result = $this->getDadataClient()->getAddressById($fias);
                // если ничего не нашлось, result будет === null.
                // мы кешируем и такой ответ, потому что если сейчас по этому fias
                // ничего нет, то и завтра не будет.
                // Если же будет ошибка запроса, то будет Exception и кеш не сохранится.
                return $result;
            },
            60 * 60 * 24 * 30
        );

        if (!$data) {
            return false;
        }

        $currentLat = $data->geo_lat;
        $currentLng = $data->geo_lon;

        $distanceM = $this->vincentyGreatCircleDistance($lat, $lng, $currentLat, $currentLng);

        if ($distanceM < 10000) {
            // расстояние менее 10 км, значит это наш город
            return true;
        }

        return false;
    }

    protected $_byId = [];
    protected function getById($id) {
        if (!array_key_exists($id, $this->_byId)) {
            $this->_byId[$id] = CdekCity::findOne($id);
        }

        return $this->_byId[$id];
    }

    protected $_byCityCode = [];
    public function getByCityCode($cityCode) {
        if (!array_key_exists($cityCode, $this->_byCityCode)) {
            $cdekCity = CdekCity::findOne(['city_code' => $cityCode]);
            if ($cdekCity) {
                $this->_byCityCode[$cityCode] = $cdekCity->id;
                if (!array_key_exists($cdekCity->id, $this->_byId)) {
                    $this->_byId[$cdekCity->id] = $cdekCity;
                }
            } else {
                $this->_byCityCode[$cityCode] = null;
            }
        }

        return $this->_byCityCode[$cityCode] ? $this->getById($this->_byCityCode[$cityCode]) : null;
    }

    protected $_byNameVariants = [];
    protected function findByName($name) {
        // е/ё - ищем по всем возможным вариантам
        $nameVariants = $this->getNameVariants($name);

        // ключ - $nameVariants, а не $name, потому что $name не нормализован по е/ё,
        // в отличие от $nameVariants
        sort($nameVariants);
        $key = md5(serialize($nameVariants));

        if (!array_key_exists($key, $this->_byNameVariants)) {
            $ids = [];

            $query = CdekCity::find()
                ->where(['name' => $nameVariants])
            ;

            foreach ($nameVariants as $nameVariant) {
                // некоторые города имеют приписку к CityName, например
                // "FullName":"Железногорск, Красноярский край","CityName":"Железногорск, Красноярский край"
                $query->orWhere(['like', 'name', $nameVariant . ',%', false]);
                // Иногда они имеют приписку и без запятой - через пробел, например
                // "FullName":"Атырау (Гурьев)","CityName":"Атырау (Гурьев)"
                $query->orWhere(['like', 'name', $nameVariant . ' %', false]);
            }

            $cities = $query->all();

            foreach ($cities as $city) {
                if (!array_key_exists($city->id, $this->_byId)) {
                    $this->_byId[$city->id] = $city;
                }

                $ids[] = $city->id;
            }
            $this->_byNameVariants[$key] = $ids;
        }

        $ids = $this->_byNameVariants[$key];

        $result = [];

        foreach ($ids as $id) {
            if ($city = $this->getById($id)) {
                $result[] = $city;
            }
        }

        return $result;
    }

    public function countryIsoToCdekCountryCode() {
        return [
            'AM' => 41,
            'UA' => 47,
            'RU' => 1,
            'KG' => 15,
            'KZ' => 48,
            'BY' => 42,
        ];
    }

    /**
     * Этот метод ищет точное совпадение по имени города, стране и геокоординатам.
     * Если не удалось всеми доступными способами определить, что найденный город является
     * искомым (например, выпало два возможных варианта), или не удалось найти город вообще
     * - возвращает null.
     * порядок lat, lng, как в Google Maps API
     * @param $name
     * @param $lat
     * @param $lng
     * @param null $countryIso
     * @return CdekCity|mixed|null
     */
    public function getByNameLatLng($name, $lat, $lng, $countryIso) {
        /** @var $cdekCities CdekCity[] */

        $countryCode = @$this->countryIsoToCdekCountryCode()[$countryIso];

        if (!$countryCode) {
            return null;
        }

        $cdekCities = $this->findByName($name);

        if (!$cdekCities) {
            return null;
        }



        // 1. фильтруем по стране
        foreach ($cdekCities as $k => $cdekCity) {
            if ($cdekCity->data['CountryCode'] != $countryCode) {
                unset($cdekCities[$k]);
            }
        }



        if ($countryCode !== 1) {
            // $countryCode == 1 - это Россия
            // Если это не Россия, то кроме совпадения имени ничего не проверить.
            // Сразу возваращаем вариант, если он единственный, или ничего,
            // если вариантов несколько.

            // Впрочем, сначала попробуем решить известные коллизии
            if (count($cdekCities) > 1) {
                $one = $this->resolveFindCollisionManual($cdekCities, $name, $lat, $lng, $countryIso);
                if ($one) {
                    return $one;
                }
            }

            return (count($cdekCities) === 1) ? array_shift($cdekCities) : null;
        }




        // Ок, осталась Россия. Дополнительно проверим по FIAS и геокоординатам
        foreach ($cdekCities as $k => $cdekCity) {
            $fias = @$cdekCity->data['FIAS'];

            if (!$fias) {
                // странно, у CDEK для российских городов должен быть FIAS...
                unset($cdekCities[$k]);
                continue;
            }

            if (!$this->isMatchesByFiasLatLng($fias, $lat, $lng)) {
                // на всякий случай проверяем все города, а не останавливаемся на
                // первом совпавшем - вдруг совпадений будет несколько, тогда нужно
                // будет вернуть null, а не город.
                unset($cdekCities[$k]);
                continue;
            }
        }

        // Если подошел один и только один город, возвращаем его, если нет - поиск не удался.
        return (count($cdekCities) === 1) ? array_shift($cdekCities) : null;
    }

    protected function resolveFindCollisionManual($variants, $name, $lat, $lng, $countryIso) {
        $variants = ArrayHelper::index($variants, 'city_code');
        $cityCodes = array_keys($variants);
        sort($cityCodes);

        switch (true) {
            case $cityCodes == [13435, 23865]:
                // 23865 - посёлок Актау (Темиртау), Карагандинская обл.
                // 13435 - город Актау, Мангистауская  обл
                if (floor($lat) == 43 && floor($lng) == 51) {
                    return $variants[13435];
                }
                break;
        }

        return null;
    }

    protected function getNameVariants($name) {
        // такая проблема, вернее, две:
        // 1. Город может быть написан как с Е, так и с Ё
        // 2. Mysql не поддерживает regexp по utf8.
        // Приходится изворачиваться.
        $variants = [''];
        for ($i = 0; $i < mb_strlen($name); $i++) {
            $char = mb_substr($name, $i, 1);
            if (in_array($char, ['е', "Е", "ё", "Ё"])) {
                $newVariants = $variants;
                foreach ($newVariants as &$variant) {
                    $variant .= 'е';
                }
                unset($variant);
                foreach ($variants as &$variant) {
                    $variant .= 'ё';
                }
                unset($variant);
                $variants = array_merge($variants, $newVariants);
            } else {
                foreach ($variants as &$variant) {
                    $variant .= $char;
                }
                unset($variant);
            }
        }

        return $variants;
    }

    /**
     * https://stackoverflow.com/a/10054282/1775065
     *
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    protected function vincentyGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000.0)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

}