<?php namespace Picqer\Carriers;

use GuzzleHttp\Client;

class Transsmart {

    private $username;
    private $password;

    private $testMode = false;

    private $apiLocation = 'https://connect.api.transwise.eu/Api';
    private $apiTestLocation = 'https://connect.test.api.transwise.eu/Api';

    /**
     * @var Client
     */
    private $client;

    public function __construct($username, $password, Client $client)
    {
        $this->username = $username;
        $this->password = $password;
        $this->client = $client;

        $this->setClientDefaults();
    }

    private function setClientDefaults()
    {
        $this->client->setDefaultOption('auth', array($this->username, $this->password));
    }

    /**
     * Switch test API
     *
     * @param boolean $bool
     */
    public function setTestMode($bool)
    {
        $this->testMode = $bool;
    }

    private function apiLocation()
    {
        $this->testMode ? $this->apiLocation : $this->apiTestLocation;
    }

    private function sendRequest($endpoint, $method = 'GET', $payload = array())
    {
        $request = $this->client->createRequest($method, $this->apiLocation . $endpoint);

        if ($method == 'GET')
        {
            $request->setQuery($payload);
        } else
        {
            $request->setBody($payload);
        }

        $response = $this->client->send($request);

        return $response->json();
    }

    public function getCarriers()
    {
        return $this->sendRequest('/Carrier');
    }

}