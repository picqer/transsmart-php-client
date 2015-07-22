<?php namespace Picqer\Carriers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class Transsmart
{

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


    public function __construct($username, $password, $testmode = false, TranssmartLogger $logger = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->logger   = $logger ?: new TranssmartLogger;

        $this->setTestMode($testmode);

        $this->client = new Client([
            'auth'   => [ $this->username, $this->password ],
            'verify' => false
        ]);
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
     *
     * @throws TranssmartException
     * @return Response
     */
    private function get($endpoint)
    {
        $endpoint = $this->apiLocation() . $endpoint;
        $this->logger->setRequestUrl($endpoint);

        try {
            $result = $this->client->get($endpoint);

            $contents = $result->getBody()->getContents();

            $this->logger->setResponseCode($result->getStatusCode());
            $this->logger->setResponseData((string) $contents);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->logger->setResponseCode($e->getResponse()->getStatusCode());
                $this->logger->setResponseData((string) $e->getResponse()->getBody()->getContents());

                throw new TranssmartException($e->getResponse()->getBody()->getContents());
            } else {
                $this->logger->setResponseCode(null);
                $this->logger->setResponseData('Transsmart error (no message provided)');

                throw new TranssmartException('Transsmart error (no message provided): ' . $e->getResponse()->getBody()->getContents());
            }

        }

        return json_decode($contents, true);
    }


    /**
     * Send a POST request
     *
     * @param $endpoint
     * @param $form_params
     *
     * @throws TranssmartException
     * @return Response
     */
    private function post($endpoint, $form_params)
    {
        $endpoint = $this->apiLocation() . $endpoint;

        try {
            $this->logger->setRequestUrl($endpoint);
            $this->logger->setRequestData(json_encode($form_params));

            $result = $this->client->post($endpoint, [ 'form_params' => $form_params ]);

            $contents = $result->getBody()->getContents();

            $this->logger->setResponseCode($result->getStatusCode());
            $this->logger->setResponseData((string) $contents);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->logger->setResponseCode($e->getResponse()->getStatusCode());
                $this->logger->setResponseData((string) $e->getResponse()->getBody()->getContents());

                throw new TranssmartException($e->getResponse()->getBody()->getContents());
            } else {
                $this->logger->setResponseCode(null);
                $this->logger->setResponseData('Transsmart error (no message provided)');

                throw new TranssmartException('Transsmart error (no message provided): ' . $e->getResponse()->getBody()->getContents());
            }
        }

        return json_decode($contents, true);
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

        return $this->post('/Document?' . http_build_query($queryParams), $params);
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
        return $this->get('/LoginToken?expiration=3600');
    }


    public function getDocument($id)
    {
        return $this->get('/Document?id=' . $id);
    }


    /**
     * @return TranssmartLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

}