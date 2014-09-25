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
        return $this->testMode ? $this->apiTestLocation : $this->apiLocation;
    }

    /**
     * Send a GET request
     *
     * @param $endpoint
     * @param array $params
     * @return \GuzzleHttp\Message\Response
     */
    private function get($endpoint, array $params = [])
    {
        $request = $this->client->createRequest('GET', $this->apiLocation() . $endpoint);
        $query = $request->getQuery();

        foreach ($params as $paramName => $paramValue)
        {
            $query->set($paramName, $paramValue);
        }

        return $this->client->send($request);
    }

    /**
     * Send a POST request
     *
     * @param $endpoint
     * @param $body
     *
     * @return \GuzzleHttp\Message\Response
     */
    private function post($endpoint, $body)
    {
        return $this->client->post($this->apiLocation() . $endpoint, ['body' => $body]);
    }

    public function getCarriers()
    {
        return $this->get('/Carrier');
    }

    public function getCarrierProfiles()
    {
        return $this->get('/CarrierProfile');
    }

    public function getCarrierProfile($id)
    {
        return $this->get('/CarrierProfile/' . $id);
    }

    public function getShipmentLocations()
    {
        return $this->get('/ShipmentLocation');
    }

    public function getShipmentLocation($id)
    {
        return $this->get('/ShipmentLocation/' . $id);
    }

    public function createDocument(array $params, $autoBook = false, $autoLabel = false, $labelUser = null)
    {
        $queryParams = [
            'autobook'   => intval($autoBook),
            'autolabel'  => intval($autoLabel),
            'label_user' => $labelUser
        ];

        return $this->post(
            '/Document?' . http_build_query($queryParams),
            $params
        );
    }

    public function bookDocument($id)
    {
        return $this->get('/DoBooking/' . $id);
    }

    public function labelDocument($id, $pdf = false)
    {
        $queryParams = [
            'username' => $this->username,
            'pdf'      => intval($pdf)
        ];

        return $this->get('/DoLabel/' . $id . '?' . http_build_query($queryParams));
    }

    public function bookAndPrintDocument($id)
    {
        $queryParams = [
            'id'       => $id,
            'username' => $this->username
        ];

        return $this->get('/DoBookAndPrint?' . http_build_query($queryParams));
    }

    public function login()
    {
        return $this->get('/LoginToken', 'GET', array(
            'expiration' => 3600
        ));
    }

}