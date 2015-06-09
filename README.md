## interlinkexpress-api-class

Small PHP class to interface with Geopost/DPD/interlink shipping API

Was originally wrote for interlink who only have 6 services running ATM, Geopost / DPD may have more available.

### USAGE:

First create an interlink object using your URL / username / password / account no.

$shippingObject = new interlink("URL", "USERNAME", "PASSWORD", "ACC_NO");

Functions working are as follows;

listCountry();
getCountry($countrycode);
getNetcode();
insertShipping($shippingArray)

Shipping data array should be formatted to the following (see example in code)

```
$shippingArray = array( 'job_id' => NULL,
                  'collectionOnDelivery' => NULL,
                  'invoice'=> NULL,
                  'collectionDate' => '2015-6-4T05:00:00',
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
                                        //'postcode' => 'B66 1BY',
                                        //'street' => 'Roebuck Lane',
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
                                        //'postcode'=> 'B66 1BY',
                                        //'street'=> 'Roebuck Lane',
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


```

### TODO

List of services.
Get labels.
Custom requests.

enjoy.
