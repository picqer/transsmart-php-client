<?php namespace Picqer\Carriers;

class TranssmartLogger {

    protected $requestUrl = '';

    protected $requestData = '';

    protected $responseCode = '';

    protected $responseData = '';

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * @param string $requestUrl
     */
    public function setRequestUrl($requestUrl)
    {
        $this->requestUrl = $requestUrl;
    }

    /**
     * @return string
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @param string $requestData
     */
    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;
    }

    /**
     * @return string
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param string $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    /**
     * @return string
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @param string $responseData
     */
    public function setResponseData($responseData)
    {
        $this->responseData = $responseData;
    }

}