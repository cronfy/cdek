<?php
/**
 * Created by PhpStorm.
 * User: cronfy
 * Date: 27.09.17
 * Time: 16:07
 */

namespace cronfy\cdek;

use GuzzleHttp\Client;
use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * @property Client $client
 * @property string $sessionId
 */
class Api extends BaseObject
{

    // auth
    public $authLogin;
    public $authPassword;

    public $useMock;

    public $debug;

    /**
     * @return Client|MockClient
     */
    protected function getClient()
    {
        if ($this->useMock) {
            return new MockClient();
        }
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->getBaseUri(),
            // You can set any number of default request options.
            'timeout'  => 10.0,
            'debug' => $this->debug,
        ]);

        return $client;
    }

    protected function getBaseUri()
    {
        return 'http://api.cdek.ru';
    }

    protected function getDefaultPayload($payload = [])
    {
        $result = [
            'version' => '1.0'
        ];

        if ($this->authLogin || $this->authPassword) {
            $result['authLogin'] = $this->authLogin;
            $result['secure'] = md5($payload['dateExecute'] . '&'. $this->authPassword);
        }

        return $result;
    }

    protected function request($method, $uri, $queryArgs = [], $payload = null)
    {
        $options = ['query' => [], 'json' => $this->getDefaultPayload($payload)];

        if ($queryArgs) {
            $options['query'] = array_merge($options['query'], $queryArgs);
        }
        if ($payload) {
            $options['json'] = array_merge($options['json'], $payload);
        }

//        D($options);
        return $this->client->request($method, $uri, $options);
    }


    public function calculate($payload = [])
    {
        $result = $this->request('POST', '/calculator/calculate_price_by_json.php', [], $payload);
//        D((string) $result->getBody());
        return Json::decode($result->getBody());
    }
}
