<?php

namespace HomeCredit\OneClickApi\RestClient;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\NullAuthorizationProcess;
use HomeCredit\OneClickApi\ARestClientTest;

/**
 * Class InstallmentsCalculatorIntegrationTest
 * @package HomeCredit\OneClickApi\RestClient
 * @group Int
 */
class InstallmentsCalculatorIntegrationTest extends ARestClientTest
{

	protected function setUp()
	{
		self::$testType = self::TEST_TYPE_INTEGRATION;
		parent::setUp();
	}

	public function testCalculateInstallmentProgramDownPayment()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$apiClient = new InstallmentsCalculator($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var CalculateInstallmentProgramsDownPaymentsRequest $apiRequest */
		$apiRequest = CalculateInstallmentProgramsDownPaymentsRequest::fromArray(json_decode($this->getFileContent('fixtures/CalculateInstallmentProgramsDownPaymentsRequest.json'), true));
		/** @var CalculateInstallmentProgramsDownPaymentsResponse $apiResponse */
		$apiResponse = $apiClient->calculateInstallmentProgramDownPayment($apiRequest);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/installmentProgramsDownPayments', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse', $apiResponse);
	}

	public function testCalculateInstallmentProgramsOffer()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$apiClient = new InstallmentsCalculator($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var CalculateInstallmentProgramsOfferRequest $apiRequest */
		$apiRequest = CalculateInstallmentProgramsOfferRequest::fromArray(json_decode($this->getFileContent('fixtures/CalculateInstallmentProgramsOfferRequest.json'), true));
		/** @var CalculateInstallmentProgramsOfferResponse $apiResponse */
		$apiResponse = $apiClient->calculateInstallmentProgramsOffer($apiRequest);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/installmentProgramsOffer', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse', $apiResponse);
	}

	public function testCalculateInstallmentProgramDetail()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$apiClient = new InstallmentsCalculator($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var CalculateInstallmentProgramDetailRequest $apiRequest */
		$apiRequest = CalculateInstallmentProgramDetailRequest::fromArray(json_decode($this->getFileContent('fixtures/CalculateInstallmentProgramDetailRequest.json'), true));
		/** @var CalculateInstallmentProgramDetailResponse $apiResponse */
		$apiResponse = $apiClient->calculateInstallmentProgramDetail($apiRequest);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/installmentPrograms', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse', $apiResponse);
	}

}
