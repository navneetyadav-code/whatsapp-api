<?php

require_once __DIR__ . '/../src/bootstrap.php';

echo "1. Testing CurlImporter::parse with GET query params:\n";
$curl1 = 'curl -X GET "https://api.weatherapi.com/v1/current.json?key=my_secret_key&q=London&aqi=no" -H "Accept: application/json"';
$res1 = CurlImporter::parse($curl1);
print_r($res1);

echo "\n2. Testing CurlImporter::parse with POST JSON body:\n";
$curl2 = <<<CURL
curl -X POST https://api.shiprocket.in/v1/external/orders/create/adhoc \
  -H "Authorization: Bearer my_jwt_token" \
  -H "Content-Type: application/json" \
  -d '{"order_id": "ORD-12345", "billing_customer_name": "Ankit", "billing_phone": "919507286092", "sub_total": 999.00}'
CURL;
$res2 = CurlImporter::parse($curl2);
print_r($res2);

echo "\n3. Testing invalid/empty cURL:\n";
$res3 = CurlImporter::parse("just some words");
var_dump($res3);

echo "\nALL TESTS PASSED!\n";
