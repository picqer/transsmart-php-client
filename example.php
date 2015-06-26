<?php

require 'vendor/autoload.php';

use Picqer\Carriers\Transsmart;

$client = new Transsmart('', '', true);

$documentData = [
    'Reference'      => 'testingddd123123',
    'RefOrder'       => 'reference',
    'AddressName'    => 'Picqert',
    'AddressContact' => 'Stephan Groen',
    'AddressZipcode' => '6981 AR',
    'AddressCity'    => 'Doesburg',
    'AddressCountry' => 'NL',
    'AddressEmail'   => 'stephan@picqer.com',
    'AddressPhone'   => '0313482375'
];

$documentData['AddressStreet'] = 'Koepoortstraat';
$documentData['AddressStreetNo'] = '27';

$documentData['ColliInformation'][] = [
    'PackagingType' => 'BOX',
    'Description'   => 'ShipmentDescription',
    'Quantity'      => 1,
    'Weight'        => 1000 // in kg weight per box, instead of total weight in grams
];


$document = $client->createDocument($documentData);

var_dump($document);
