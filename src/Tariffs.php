<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 03.01.18
 * Time: 17:47
 */

namespace cronfy\cdek;

use yii\helpers\ArrayHelper;

class Tariffs
{
    const MODE_DOOR_DOOR = 1;
    const MODE_DOOR_WAREHOUSE = 2;
    const MODE_WAREHOUSE_DOOR = 3;
    const MODE_WAREHOUSE_WAREHOUSE = 4;

    const GROUP_PACKAGE = 1;
    const GROUP_ECONOMY_PACKAGE = 2;
    const GROUP_IN_POST = 3;
    const GROUP_CDEK_EXPRESS = 4;
    const GROUP_EXPRESS = 5;
    const GROUP_ECONOMY_DELIVERY= 6;


    public static function getAll()
    {
        // Набрано вручную из документации
        // TODO отрефакторить на API
        // P.S. А вот и нельзя:
        // https://www.cdek.ru/faq_integrator.html
        // Можно ли получить список всех доступных тарифов через API?
        // Нет, список тарифов приведён только в документации в прилагаемом архиве.

        $tariffList = [
            // Посылка

            // Услуга экономичной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.
            [
                'id' => 136,
                'im' => true,
                'weight_limit' => 30,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_PACKAGE,
            ],
            [
                'id' => 137,
                'im' => true,
                'weight_limit' => 30,
                'mode' => static::MODE_WAREHOUSE_DOOR,
                'group' => static::GROUP_PACKAGE,
            ],
            [
                'id' => 138,
                'im' => true,
                'weight_limit' => 30,
                'mode' => static::MODE_DOOR_WAREHOUSE,
                'group' => static::GROUP_PACKAGE,
            ],
            [
                'id' => 139,
                'im' => true,
                'weight_limit' => 30,
                'mode' => static::MODE_DOOR_DOOR,
                'group' => static::GROUP_PACKAGE,
            ],

            // Экономичная посылка

            // Услуга экономичной наземной доставки товаров по России для компаний, осуществляющих дистанционную торговлю.
            // Услуга действует по направлениям из Москвы в подразделения СДЭК, находящиеся за Уралом и в Крым.
            [
                'id' => 233,
                'im' => true,
                'weight_limit' => 50,
                'mode' => static::MODE_WAREHOUSE_DOOR,
                'group' => static::GROUP_ECONOMY_PACKAGE,
            ],
            [
                'id' => 234,
                'im' => true,
                'weight_limit' => 50,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_ECONOMY_PACKAGE,
            ],

            // До постомата InPost

            // Услуга доставки товаров по России с использованием постоматов. Для компаний, осуществляющих дистанционную торговлю.
            // Характеристики услуги:
            //      - по услуге принимаются только одноместные заказы
            //      - выбранный при оформлении заказа постомат изменить на другой нельзя
            //      - при невозможности использования постоматов осуществляется доставка до ПВЗ СДЭК или «до двери» клиента с изменением тарификации на услугу «Посылка»
            //      - срок хранения заказа в ячейке: 5 дней с момента закладки в постомат
            //      - возможность приема наложенного платежа

            // 3 вида ячеек:
            // А (8*38*64 см)— до 5 кг
            // В (19*38*64 см) — до 7 кг
            // С (41*38*64 см)— до 20 кг

            [
                'id' => 301,
                'im' => true,
                'weight_limit' => 20,
                'mode' => static::MODE_DOOR_WAREHOUSE,
                'group' => static::GROUP_IN_POST,
            ],
            [
                'id' => 302,
                'im' => true,
                'weight_limit' => 20,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_IN_POST,
            ],

            // CDEK Express

            // Сервис по доставке товаров из-за рубежа в России с услугами по таможенному оформлению.
            // Три варианта предоставления услуги:
            // 1) Мы забираем из другой страны, ввозим в РФ, проходим таможню, доставляем - накладная оформляется, например как Пекин-Новосибирск
            // 2) Клиент сам ввозит груз в Россию, мы проходим российскую таможню и доставляем - накладная оформляется, например как Москва-Новосибирск
            // 3) Клиент сам ввозит груз в Россию, сам проходит российскую таможню, мы только доставляем - накладная оформляется, например как Москва-

            [
                'id' => 291,
                'im' => true,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_CDEK_EXPRESS,
            ],
            [
                'id' => 293,
                'im' => true,
                'mode' => static::MODE_DOOR_DOOR,
                'group' => static::GROUP_CDEK_EXPRESS,
            ],
            [
                'id' => 294,
                'im' => true,
                'mode' => static::MODE_WAREHOUSE_DOOR,
                'group' => static::GROUP_CDEK_EXPRESS,
            ],
            [
                'id' => 295,
                'im' => true,
                'mode' => static::MODE_DOOR_WAREHOUSE,
                'group' => static::GROUP_CDEK_EXPRESS,
            ],

            // Экспресс

            // Классическая экспресс-доставка по России документов и грузов до 30 кг.

            [
                'id' => 1,
                'weight_limit' => 30,
                'mode' => static::MODE_DOOR_DOOR,
                'group' => static::GROUP_EXPRESS,
            ],

            // Экономичный экспресс
            // Недорогая доставка грузов по России ЖД и автотранспортом (доставка грузов с увеличением сроков).

            [
                'id' => 5,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_ECONOMY_DELIVERY,
            ],

            // Экспресс лайт

            // Классическая экспресс-доставка по России документов и грузов.

            [
                'id' => 10,
                'weight_limit' => 30,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_EXPRESS,
            ],

            [
                'id' => 11,
                'weight_limit' => 30,
                'mode' => static::MODE_WAREHOUSE_DOOR,
                'group' => static::GROUP_EXPRESS,
            ],

            [
                'id' => 12,
                'weight_limit' => 30,
                'mode' => static::MODE_DOOR_WAREHOUSE,
                'group' => static::GROUP_EXPRESS,
            ],

            [
                'id' => 15,
                'weight_limit' => 30,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_EXPRESS,
            ],

            // Магистральный экспресс
            // Быстрая экономичная доставка грузов по России

            [
                'id' => 15,
                'mode' => static::MODE_WAREHOUSE_WAREHOUSE,
                'group' => static::GROUP_ECONOMY_DELIVERY,
            ],

        ];

        foreach ($tariffList as &$item) {
            if (!isset($item['im'])) {
                $item['im'] = false;
            }
        }
        unset($item);

        return ArrayHelper::index($tariffList, 'id');
    }

    public static function getMatching($criteria)
    {
        $criteria = array_merge([
            'ids' => [],
            'modes' => [],
        ], $criteria);

        $result = [];
        foreach (static::getAll() as $tariff) {
            if ($criteria['ids'] && !in_array($tariff['id'], $criteria['ids'])) {
                continue;
            }
            if ($criteria['modes'] && !in_array($tariff['mode'], $criteria['modes'])) {
                continue;
            }
            $result[$tariff['id']] = $tariff;
        }

        return $result;
    }
}
