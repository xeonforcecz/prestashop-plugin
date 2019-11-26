<?php

namespace HomeCredit\OneClickApi\RestClient;

use GuzzleHttp6\Psr7\Response;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\NullAuthorizationProcess;
use HomeCredit\OneClickApi\ARestClientTest;

/**
 * Class InstallmentsCalculatorTest
 * @package HomeCredit\OneClickApi\RestClient
 * @group Unit
 */
class InstallmentsCalculatorTest extends ARestClientTest
{

	protected function setUp()
	{
		self::$testType = self::TEST_TYPE_LOCAL_MOCK;
		self::$testVsMockMap = [
			static::class . '::testCalculateInstallmentProgramDownPayment' => [
				new Response(200, [], $this->getFileContent('fixtures/CalculateInstallmentProgramsDownPaymentsResponse.json'))
			],
			static::class . '::testCalculateInstallmentProgramsOffer' => [
				new Response(200, [], $this->getFileContent('fixtures/CalculateInstallmentProgramsOfferResponse.json'))
			],
			static::class . '::testCalculateInstallmentProgramDetail' => [
				new Response(200, [], $this->getFileContent('fixtures/CalculateInstallmentProgramDetailResponse.json'))
			],
		];
		parent::setUp();
	}

	public function testCalculateInstallmentProgramDownPayment()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$apiClient = new InstallmentsCalculator($httpClientFactory, new NullAuthorizationProcess());
		/** @var CalculateInstallmentProgramsDownPaymentsRequest $apiRequest */
		$apiRequest = CalculateInstallmentProgramsDownPaymentsRequest::fromArray(json_decode($this->getFileContent('fixtures/CalculateInstallmentProgramsDownPaymentsRequest.json'), true));
		/** @var CalculateInstallmentProgramsDownPaymentsResponse $apiResponse */
		$apiResponse = $apiClient->calculateInstallmentProgramDownPayment($apiRequest);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/installmentProgramsDownPayments', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse', $apiResponse);
		$this->assertEquals(50000, $apiResponse->getStep()->getAmount());
	}

	public function testCalculateInstallmentProgramsOffer()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$apiClient = new InstallmentsCalculator($httpClientFactory, new NullAuthorizationProcess());
		/** @var CalculateInstallmentProgramsOfferRequest $apiRequest */
		$apiRequest = CalculateInstallmentProgramsOfferRequest::fromArray(json_decode($this->getFileContent('fixtures/CalculateInstallmentProgramsOfferRequest.json'), true));
		/** @var CalculateInstallmentProgramsOfferResponse $apiResponse */
		$apiResponse = $apiClient->calculateInstallmentProgramsOffer($apiRequest);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/installmentProgramsOffer', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse', $apiResponse);
		$this->assertTrue(count($apiResponse->getInstallmentPrograms()) == 1);
	}

	public function testCalculateInstallmentProgramDetail()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$apiClient = new InstallmentsCalculator($httpClientFactory, new NullAuthorizationProcess());
		/** @var CalculateInstallmentProgramDetailRequest $apiRequest */
		$apiRequest = CalculateInstallmentProgramDetailRequest::fromArray(json_decode($this->getFileContent('fixtures/CalculateInstallmentProgramDetailRequest.json'), true));
		/** @var CalculateInstallmentProgramDetailResponse $apiResponse */
		$apiResponse = $apiClient->calculateInstallmentProgramDetail($apiRequest);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/installmentPrograms', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse', $apiResponse);
		$this->assertEquals('COCONL06', $apiResponse->getProductCode());
	}
}
