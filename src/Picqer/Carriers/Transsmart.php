<?php namespace Picqer\Carriers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

class Transsmart {

    /**
     * @var TranssmartLogger
     */
    protected $logger;

    private $username;
    private $password;

    private $testMode = false;

    private $apiLocation = 'https://connect.api.transwise.eu/Api';
    private $apiTestLocation = 'https://connect.test.api.transwise.eu/Api';

    /**
     * @var Client
     */
    private $client;

    public function __construct($username, $password, Client $client, TranssmartLogger $logger = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->client = $client;
        $this->logger = $logger ?: new TranssmartLogger;

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
     * @throws TranssmartException
     * @return Response
     */
    private function get($endpoint, array $params = [])
    {
        $request = $this->client->createRequest('GET', $this->apiLocation() . $endpoint);
        $query = $request->getQuery();

        foreach ($params as $paramName => $paramValue)
        {
            $query->set($paramName, $paramValue);
        }

        $this->logger->setRequestUrl($request->getUrl());
        $this->logger->setRequestData((string)$query);

        try
        {
            $result = $this->client->send($request);

            $this->logger->setResponseCode($result->getStatusCode());
            $this->logger->setResponseData((string)$result->getBody());
        } catch (RequestException $e)
        {
            if ($e->hasResponse())
            {
                $this->logger->setResponseCode($e->getResponse()->getStatusCode());
                $this->logger->setResponseData((string)$e->getResponse()->getBody());

                throw new TranssmartException($e->getResponse()->getBody());
            } else
            {
                $this->logger->setResponseCode(null);
                $this->logger->setResponseData('Transsmart error (no message provided)');

                throw new TranssmartException('Transsmart error (no message provided): ' . $e->getResponse());
            }

        }

        return $result->json();
    }

    /**
     * Send a POST request
     *
     * @param $endpoint
     * @param $body
     *
     * @throws TranssmartException
     * @return Response
     */
    private function post($endpoint, $body)
    {
        try
        {
            $this->logger->setRequestUrl($this->apiLocation() . $endpoint);
            $this->logger->setRequestData(json_encode($body));

            $result = $this->client->post($this->apiLocation() . $endpoint, ['body' => $body]);

            $this->logger->setResponseCode($result->getStatusCode());
            $this->logger->setResponseData((string)$result->getBody());
        } catch (RequestException $e)
        {
            if ($e->hasResponse())
            {
                $this->logger->setResponseCode($e->getResponse()->getStatusCode());
                $this->logger->setResponseData((string)$e->getResponse()->getBody());

                throw new TranssmartException($e->getResponse()->getBody());
            } else
            {
                $this->logger->setResponseCode(null);
                $this->logger->setResponseData('Transsmart error (no message provided)');

                throw new TranssmartException('Transsmart error (no message provided): ' . $e->getResponse());
            }
        }

        return $result->json();
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

    public function getServiceLevelTimes()
    {
        return $this->get('/ServiceLevelTime');
    }

    public function getServiceLevelTime($id)
    {
        return $this->get('/ServiceLevelTime/' . $id);
    }

    public function getServiceLevelOthers()
    {
        return $this->get('/ServiceLevelOther');
    }

    public function ServiceLevelOther($id)
    {
        return $this->get('/ServiceLevelTime/' . $id);
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

    public function labelDocument($id, $pdf = false, $downloadOnly = false)
    {
        $queryParams = [
            'username'     => $this->username,
            'pdf'          => intval($pdf),
            'downloadonly' => intval($downloadOnly)
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
        return $this->get('/LoginToken', array(
            'expiration' => 3600
        ));
    }

    public function getDocument($id)
    {
        return $this->get('/Document', array(
            'id' => $id
        ));
    }

    /**
     * @return TranssmartLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

}