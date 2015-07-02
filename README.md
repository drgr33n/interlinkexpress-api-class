## interlinkexpress-api-class

Small PHP class to interface with Geopost/DPD/interlink shipping API

Was originally wrote for interlink who only have 6 services running ATM, Geopost / DPD may have more available.

### USAGE:

First create an interlink object using your URL / username / password / account no.

$shippingObject = new interlink("URL", "USERNAME", "PASSWORD", "ACC_NO");

Functions working are as follows;

####getShipping($dataArray);

Get shipping returns a list of available services based on your account & collection / delivery data. Array format is as follows;

```
$dataArray = array(
        'collectionDetails' => [
                'address' => [
                        'locality' => 'Birmingham',
                        'county' => 'West Midlands',
                        'ostcode' => 'B661BY',
                        'countryCode' => 'GB'
                ],
        ],
        'deliveryDetails' => [
                'address' => [
                        'locality' => 'Birmingham',
                        'county' => 'West Midlands',
                        'postcode' => 'B11AA',
                        'countryCode' => 'GB'
                ],
        ],
        'deliveryDirection' => 1,
        'numberOfParcels' => 1,
        'totalWeight' => 5,
        'shipmentType' => 0
        );
```

####listCountry();

Provides a full list of available shipping countries.


####getCountry($countrycode);

This function brings back the country details for a provided country code and can be used to determine if a
country requires a postcode or if liability is allowed.

####getNetcode($geoCode);

Retrieves the supported countries for a geoServiceCode.


####insertShipping($shippingArray);

Function to insert shipping into your account. Please refer to your API guide for more info. Shipping data array should be formatted to the following;

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
                        ]]
		);

```

People are reporting in versions of PHP < 5.6 double brackets are rejected. If thats the case please change to the following;

```
$shippingArray = array( 'job_id' => NULL,
                  'collectionOnDelivery' => NULL,
                  'invoice'=> NULL,
                  'collectionDate' => '2015-6-4T05:00:00',
                  'consolidate' => NULL,
                  'consignment' => array(array(
                        'consignmentNumber' => NULL,
                        'consignmentRef' => NULL,
                        'parcels' => array(),
                        'collectionDetails' => array(
                                'contactDetails' => array(
                                        'contactName' => 'My Contact',
                                        'telephone' => '0121 500 2500'
                                ),
                                'address' => array(
                                        'organisation' => 'GeoPostUK Ltd',
                                        'countryCode' => 'GB',
                                        'postcode' => 'B66 1BY',
                                        'street' => 'Roebuck Lane',
                                        'locality' => 'Smethwick',
                                        'town' => 'Birmingham',
                                        'county' => 'West Midlands'
                                )
                        ),
                        'deliveryDetails'=> array(
                                'contactDetails'=> array(
                                        'contactName'=> 'My Contact',
                                        'telephone'=> '0121 500 2500'
                                ),
                                'address'=> array(
                                        'organisation'=> 'GeoPostUK Ltd',
                                        'countryCode'=> 'GB',
                                        'postcode'=> 'B66 1BY',
                                        'street'=> 'Roebuck Lane',
                                        'locality'=> 'Smethwick',
                                        'town'=> 'Birmingham',
                                        'county'=> 'West Midlands'
                                ),
                                'notificationDetails' => array(
                                        'email'=> 'my.email@geopostuk.com',
                                        'mobile'=> '07921000001'
                                )
                        ),
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
                        ))
                );

```

####customGet($str);

Allows you to send a custom get request. For example

$str="services/custom/string";
customGet($str);

####getLabel($shippmentId, $returnFormat);

Returns printer data for printing labels. I don't know how this all works, but I do know it returns printer data :P Return format oprions are as follows;

text/html
text/vnd.eltron-epl
text/vnd.citizen-clp

enjoy.
