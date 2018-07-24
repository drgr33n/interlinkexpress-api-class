<?php
/**
 * Interlink express API class
 * by drgr33n <zarren@norfolklights.com>
 * Adapted to PSR-2 by AbanteCart Team dsuprunenko@abantecart.com
 *
 */

class interlink
{
// Initialize variables
    protected $version = "Interlink express API class v1.1a";
    protected $url;
    protected $timeout = 5;
    protected $ch;
    protected $headers;
    protected $username;
    protected $password;
    protected $accountNo;
    protected $jsonSize = 0;
    protected $returnFormat = 'application/json';
    protected $contentType = 'none';
    protected $isPrintJob = 0;
    protected $payload = '';

    /**
     * interlink constructor.
     *
     * @param string $url
     * @param string $username
     * @param string $password
     * @param string|int $accountNo
     */
    public function __construct($url, $username, $password, $accountNo)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
        $this->accountNo = $accountNo;
        $this->ch = curl_init();
    }

    /**
     * @param string $timeout
     * @param array $headers
     *
     * @return array
     * @throws Exception
     */
    protected function authenticate($timeout = '5', $headers = array())
    {
        $headers = (array)$headers;
        $headers['Content-Type'] = 'Content-Type: None';
        $headers['Accept'] = 'Accept: application/json';
        $headers['Authorization'] = 'Authorization: Basic '.base64_encode($this->username.':'.$this->password);
        $headers['GEOClient'] = 'GEOClient: '.$this->username.'/'.$this->accountNo;
        $headers['Content-Length'] = 'Content-Length: 0';

        curl_setopt_array(
            $this->ch,
            array(
                CURLOPT_URL            => $this->url.'/user/?action=login',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_USERAGENT      => $this->version,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_CUSTOMREQUEST  => 'POST',
            )
        );
        $authPost = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $data = json_decode($authPost, true);
        if (curl_errno($this->ch)) {
            throw new Exception('Error connecting to API: '.curl_error($this->ch));
        } elseif ($httpCode === 401 || $httpCode === 403 || $httpCode === 404 || $httpCode === 500
            || $httpCode === 503) {
            $this->httpError($httpCode);
        } else {
            return $data['data']['geoSession'];
        }
        return array();
    }

    /**
     * @param array $headers
     *
     * @throws Exception
     */
    protected function constructHeaders($headers = array())
    {
        $headers = (array)$headers;
        $authToken = $this->authenticate();
        $this->headers = array_merge(array(
            'Content-Type: '.$this->contentType,
            'Accept: '.$this->returnFormat,
            'GEOClient: '.$this->username.'/'.$this->accountNo,
            'GEOSession: '.$authToken,
            'Content-Length: '.$this->jsonSize,
        ), $headers);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function listCountry()
    {
        $method = "GET";
        $route = "/shipping/country";
        $query = $this->doQuery($method, $route);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param $route
     *
     * @return array
     * @throws Exception
     */
    public function customGet($route)
    {
        $method = "GET";
        $query = $this->doQuery($method, $route);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getShipping($data)
    {
        $method = "GET";
        // Needs cleaning up but regex is like Chinese to me !
        $data = str_replace('%5D', '', str_replace('%5B', '.', http_build_query($data)));
        $route = "/shipping/network/?".$data;
        $query = $this->doQuery($method, $route);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param string $country - two letters country code
     *
     * @return array
     * @throws Exception
     */
    public function getCountry($country)
    {
        $method = "GET";
        $route = "/shipping/country/";
        $query = $this->doQuery($method, $route.$country);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param string $geoCode
     *
     * @return array
     * @throws Exception
     */
    public function getNetCode($geoCode)
    {
        $method = "GET";
        $route = "/shipping/network/".$geoCode;
        $query = $this->doQuery($method, $route);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param mixed $payload
     *
     * @return array
     * @throws Exception
     */
    public function insertShipping($payload)
    {
        $method = "POST";
        $route = "/shipping/shipment";
        $this->encodePayload($payload);
        $this->contentType = 'application/json';
        $query = $this->doQuery($method, $route);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param string $shipmentId
     * @param string $dataType
     *
     * @return array
     * @throws Exception
     */
    public function getLabel($shipmentId, $dataType)
    {
        $method = "GET";
        $this->returnFormat = $dataType;
        $route = "/shipping/shipment/".$shipmentId."/label/";
        $this->isPrintJob = 1;
        $this->contentType = 'Accept';
        $query = $this->doQuery($method, $route);
        if (isset($query['error'])) {
            $this->apiError($query['error']);
        }
        return $query;
    }

    /**
     * @param string $method
     * @param string $route
     *
     * @return array
     * @throws Exception
     */
    protected function doQuery($method, $route)
    {
        $this->constructHeaders();
        curl_setopt_array($this->ch, array(
            CURLOPT_URL            => $this->url.$route,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_USERAGENT      => $this->version,
            CURLOPT_HTTPHEADER     => $this->headers,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => (isset($this->payload)) ? $this->payload : null,
        ));
        $data = curl_exec($this->ch);
        $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        //If print job don't decode.
        if ($this->isPrintJob == 0) {
            $response = json_decode($data, true);
        } else {
            $response = $data;
        }
        if (curl_errno($this->ch)) {
            throw new Exception('Curl Error connecting to API: '.curl_error($this->ch));
        } elseif ($httpCode === 401 || $httpCode === 403 || $httpCode === 404 || $httpCode === 500
            || $httpCode === 503) {
            $this->httpError($httpCode);
        } else {
            return $response;
        }
        return array();
    }

// Encode payload

    /**
     * @param mixed $payload
     */
    protected function encodePayload($payload)
    {
        $this->payload = json_encode($payload);
        $this->jsonSize = strlen($this->payload);
    }

    /**
     * @param $httpCode
     *
     * @throws Exception
     */
    public function httpError($httpCode)
    {
        switch ($httpCode) {
            case '401':
                throw new Exception('Username / Password incorrect');
                break;
            case '403':
                throw new Exception('Geo-session header not found or invalid');
                break;
            case '404':
                throw new Exception('An attempt was made to call an API in which the URL cannot be found');
                break;
            case '500':
                throw new Exception('The ESG server had an internal error');
                break;
            case '503':
                throw new Exception('The API being called is temporary out of service');
                break;
        }
    }

    /**
     * @param array $err
     *
     * @throws Exception
     */
    public function apiError($err)
    {
        // Probably not the ideal solution but works and I'm not really a PHP dev. Please clean me !! :D
        if (isset($err[0])) {
            throw new Exception(
                'API Error! Code: '.$err[0]['errorCode']
                .' Type: '.$err[0]['errorType']
                .' Message: '.$err[0]['obj'].' / '.$err[0]['errorMessage']);
        } else {
            throw new Exception(
                'API Error! Code: '.$err['errorCode']
                .' Type: '.$err['errorType']
                .' Message: '.$err['obj'].' / '.$err['errorMessage']);
        }
    }

    /**
     * Destruct object
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }
}

