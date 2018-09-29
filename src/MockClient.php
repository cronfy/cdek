<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 14.03.18
 * Time: 12:00
 */

namespace cronfy\cdek;

class MockClient
{
    public function request($method, $uri, $options)
    {
        switch (true) {
            case $method == 'POST' && $uri == '/calculator/calculate_price_by_json.php':
                $response = new MockResponse();
                $response->body = '{"result":{"price":"265","deliveryPeriodMin":1,"deliveryPeriodMax":1,"deliveryDateMin":"2018-03-15","deliveryDateMax":"2018-03-15","tariffId":137,"priceByCurrency":265,"currency":"RUB"}}';
                return $response;
                break;
            default:
                throw new \Exception("Not supported mock request");
        }
    }
}
