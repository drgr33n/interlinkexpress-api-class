<?php
/*
* Interlink express API class
* by drgr33n <zarren@norfolklights.com>
*/

class interlink {
// Initialize varables
        private $version = "Interlink express API class v1.0a";
        private $url;
        private $timeout;
        private $ch;
        private $headers;
        private $username;
        private $password;
        private $accountNo;
        private $jsonSize = 0;

// Construct object
        public function __construct($url, $username, $password, $accountNo) {
                $this->url = $url;
                $this->username = $username;
                $this->password = $password;
                $this->accountNo = $accountNo;
                $this->ch = curl_init();
        }

// Do authentication
        private function authenticate($timeout='5', $headers=array()) {
                $headers = array( 'Content-Type: application/json',
                                        'Accept: application/json',
                                        'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password),
                                        'GEOClient: ' . $this->username . '/' . $this->accountNo,
                                        'Content-Length: 0'
                                        );
                curl_setopt_array($this->ch, array(
                        CURLOPT_URL => $this->url . '/user/?action=login',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => $timeout,
                        CURLOPT_USERAGENT => $this->version,
                        CURLOPT_HTTPHEADER => $headers,
                        CURLOPT_CUSTOMREQUEST => 'POST'
                        ));
                $authPost = curl_exec($this->ch);
                $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                $data = json_decode($authPost, true);
                if (curl_errno($this->ch)) {
                        throw new Exception('Error connecting to API: ' . curl_error($this->ch));
                } elseif ($httpCode === 401 || $httpCode === 403 || $httpCode === 404 || $httpCode === 500 || $httpCode === 503) {
                        $this->httpError($httpCode);
                } else {
                        return $data['data']['geoSession'];
                }
        }

// Construct headers for data transfer
        private function constructHeaders($headers=array()) {
                $authToken = $this->authenticate();
                $this->headers = array( 'Content-Type: application/json',
                                        'Accept: application/json',
                                        'GEOClient: ' . $this->username . '/' . $this->accountNo,
                                        'GEOSession: ' . $authToken,
                                        'Content-Length: ' . $this->jsonSize
                                        );
        }

// List shipping countries
        public function listCountry() {
                $method="GET";
                $reqStr="/shipping/country";
                $query = $this->doQuery($method, $reqStr);
                return isset($query['error']) ? $this->apiError($query['error']) : $query;

        }

// Get country
        public function getCountry($country) {
                $method="GET";
                $reqStr="/shipping/country/";
                $query = $this->doQuery($method, $reqStr . $country);
                return isset($query['error']) ? $this->apiError($query['error']) : $query;
        }

//Get Network Code
        public function getNetcode() {
                $method="GET";
                $reqStr="/shipping/network/812/";
                $query = $this->doQuery($method, $reqStr);
                return isset($query['error']) ? $this->apiError($query['error']) : $query;
        }

//Insert Shipping
        public function insertShipping($payload) {
                $method="POST";
                $reqStr="/shipping/shipment";
                $this->encodePayload($payload);
                $query = $this->doQuery($method, $reqStr);
                return isset($query['error']) ? $this->apiError($query['error']) : $query;
        }

//doQuery
        private function doQuery($method, $reqStr){
                $this->constructHeaders();
                curl_setopt_array($this->ch, array(
                        CURLOPT_URL => $this->url . $reqStr,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CONNECTTIMEOUT => $this->timeout,
                        CURLOPT_USERAGENT => $this->version,
                        CURLOPT_HTTPHEADER => $this->headers,
                        CURLOPT_CUSTOMREQUEST => $method,
                        CURLOPT_POSTFIELDS => (isset($this->payload)) ? $this->payload : NULL
                        ));
                $data = curl_exec($this->ch);
                $httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
                $response = json_decode($data, true);
                if (curl_errno($this->ch)) {
                        throw new Exception('Error connecting to API: ' . curl_error($this->ch));
                } elseif ($httpCode === 401 || $httpCode === 403 || $httpCode === 404 || $httpCode === 500 || $httpCode === 503) {
                        $this->httpError($httpCode);
                } else {
                        return (is_array($response) ? $response : array());
                }
        }

// Encode payload
        private function encodePayload($payload) {
                $this->payload = json_encode($payload);
                $this->jsonSize = strlen($this->payload);
        }
// Handle HTTP errors
        public function httpError($httpCode) {
                switch ($httpCode) {
                        case '401':
                                throw new Exception('Username / Password incorrect');
                                break;
                        case '403':
                                throw new Exception('Geosession header not found or invalid');
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
// Handle API errors
        public function apiError($err) {
                foreach ($err as $i) {
                        echo 'API Error! Code: ' . $i['errorCode'] . ' Type: ' . $i['errorType'] . ' Message: ' . $i['obj'] . ' ' . $i['errorMessage'] . "\r\n";
                }
                throw new Exception('API error');

                //var_dump($err);
        }

// Destruct object
        public function __destruct() {
                curl_close($this->ch);
        }
}

//EXAMPLE

$test = new interlink("https://api.interlinkexpress.com", "USERNAME", "PASSWORD", "ACC_NO");
//var_dump($test->getNetcode());


//Shipping template

$payload = array( 'job_id' => NULL,
                  'collectionOnDelivery' => NULL,
                  'invoice'=> NULL,
                  'collectionDate' => '2015-6-16T05:00:00',
                  'consolidate' => NULL,
                  'consignment' => [[
                        'consignmentNumber' => NULL,
                        'consignmentRef' => NULL,
                        'parcels' => [],
                        'collectionDetails' => [
                                'contactDetails' => [
                                        'contactName' => 'My Contact',
                                        'telephone' => '0121 500 2500'
                                ],
                                'address' => [
                                        'organisation' => 'GeoPostUK Ltd',
                                        'countryCode' => 'GB',
                                        'postcode' => 'B66 1BY',
                                        'street' => 'Roebuck Lane',
                                        'locality' => 'Smethwick',
                                        'town' => 'Birmingham',
                                        'county' => 'West Midlands'
                                ]
                        ],
                        'deliveryDetails'=> [
                                'contactDetails'=> [
                                        'contactName'=> 'My Contact',
                                        'telephone'=> '0121 500 2500'
                                ],
                                'address'=> [
                                        'organisation'=> 'GeoPostUK Ltd',
                                        'countryCode'=> 'GB',
                                        'postcode'=> 'B66 1BY',
                                        'street'=> 'Roebuck Lane',
                                        'locality'=> 'Smethwick',
                                        'town'=> 'Birmingham',
                                        'county'=> 'West Midlands'
                                ],
                                'notificationDetails' => [
                                        'email'=> 'my.email@geopostuk.com',
                                        'mobile'=> '07921000001'
                                ]
                        ],
                        'networkCode'=> '2^12',
                        'numberOfParcels'=> 1,
                        'totalWeight'=> 5,
                        'shippingRef1'=> 'My Ref 1',
                        'shippingRef2'=> 'My Ref 2',
                        'shippingRef3'=> 'My Ref 3',
                        'customsValue'=> NULL,
                        'deliveryInstructions'=> 'Please deliver with neighbour',
                        'parcelDescription'=> NULL,
                        'liabilityValue'=> NULL,
                        'liability'=> NULL
                        ]]);
var_dump($test->insertShipping($payload));
?>

