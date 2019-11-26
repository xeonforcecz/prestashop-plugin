<?php

require_once 'vendor/autoload.php';

// Zalozime si autentizacni process
$authorizationProcess = new HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess(
	'024242tech',
	'024242tech'
);

// Pripravime si tovarnu na HTTP clienta
$httpClientFactory = new \HomeCredit\OneClickApi\HttpClientFactory([
	'baseUrl' => 'https://apicz-test.homecredit.net/verdun-train/'
]);

// Vytvorime si REST clienta
$client = new \HomeCredit\OneClickApi\RestClient\Application(
	$httpClientFactory,
	$authorizationProcess
);

// Vytvorime si objekt requestu pro zalozeni application. V tomto pripade z JSONu, ulozenem na filesystemu. Pro dekodovani json je pouzita knihovna z Nette
$json = Nette\Utils\Json::decode(
	file_get_contents(__DIR__ . '/tests/fixtures/CreateApplicationRequest.json'),
	\Nette\Utils\Json::FORCE_ARRAY
);
$request = \HomeCredit\OneClickApi\Entity\CreateApplicationRequest::fromArray($json);

// Zalozime application pres API. V response budeme mi objekt HomeCredit\OneClickApi\Entity\CreateApplicationResponse
$response = $client->create($request);
var_dump($response->getId());

