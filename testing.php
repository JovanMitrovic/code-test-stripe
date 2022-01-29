<?php

require('vendor/autoload.php');

use Symfony\Component\HttpClient\HttpClient;

$httpClient = HttpClient::create();
$response = $httpClient->request('GET', 'https://demo7.zeusmanager.com/rest/symfony/technical/test.json?test_key=MMv4TWOuPh');

$content = $response->getContent();
$data = json_decode($content, true);

$clientName = $data['data']['client_name'];
$clientID = $data['data']['client_id'];

$pricePerContract = isset($data['data']['prices']['contract_price']) ? $data['data']['prices']['contract_price'] : 0.0;
$localPrice = isset($data['data']['prices']['local_price']) ? $data['data']['prices']['local_price'] : 0.0;

$localData = is_array($data['data']['local_data']) ? $data['data']['local_data'] : array();
$companyData = is_array($data['data']['company_data']) ? $data['data']['company_data'] : array();

$secretKey = 'sk_test_51Hz9cqKyKfqMDq2LG2MXKfSMrFJytkXFHVeNgBDSlifJwM12bukx8vzeBesYllgYjrhDCSoAxwX5ovsvc4jgqXTz004jMKtuul';
\Stripe\Stripe::setApiKey($secretKey);

$stripe = new \Stripe\StripeClient(
    'sk_test_51Hz9cqKyKfqMDq2LG2MXKfSMrFJytkXFHVeNgBDSlifJwM12bukx8vzeBesYllgYjrhDCSoAxwX5ovsvc4jgqXTz004jMKtuul'
);


$client = $stripe->customers->create([
    'name'          => $clientName,
    'description'   => 'My First Test Customer (created for API docs)',
]);

$clientID = $client->id;

foreach ($companyData as $company)
{
    $companyPrice = 0.0;

    $companyPrice = $company['number_of_contracts_used'] * $pricePerContract;

    foreach ($localData as $local)
    {
        if ($company['company_id'] == $local['company_id'])
        {
            $companyPrice += $localPrice;
        }
    }

    $objInvoiceItem = $stripe->invoiceItems->create(array(
                        "amount"        => (int)$companyPrice,
                        "currency"      => "usd",
                        "customer"      => $clientID,
                        "description"   => $company['company_name']
                    ));
}

echo $stripe->invoiceItems->all() . "\n";