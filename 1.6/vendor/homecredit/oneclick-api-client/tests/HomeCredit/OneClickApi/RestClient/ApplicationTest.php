<?php

namespace HomeCredit\OneClickApi\RestClient;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use HomeCredit\OneClickApi\Entity\CancelApplicationRequest;
use HomeCredit\OneClickApi\Entity\CancelApplicationResponse;
use HomeCredit\OneClickApi\Entity\ChangeApplicationOrderRequest;
use HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse;
use HomeCredit\OneClickApi\Entity\CreateApplicationRequest;
use HomeCredit\OneClickApi\Entity\CreateApplicationResponse;
use HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse;
use HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse;
use HomeCredit\OneClickApi\Entity\MarkOrderAsSentResponse;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\NullAuthorizationProcess;
use HomeCredit\OneClickApi\ARestClientTest;

/**
 * Class ApplicationTest
 * @package HomeCredit\OneClickApi\RestClient
 * @group Unit
 */
class ApplicationTest extends ARestClientTest
{

	protected function setUp()
	{
		self::$testType = self::TEST_TYPE_LOCAL_MOCK;
		self::$testVsMockMap = [
			static::class . '::testMarkOrderAsSent' => [
				new Response(200, [], $this->getFileContent('fixtures/MarkOrderAsSentResponse.json'))
			],
			static::class . '::testMarkOrderAsDelivered' => [
				new Response(200, [], $this->getFileContent('fixtures/MarkOrderAsDeliveredResponse.json'))
			],
			static::class . '::testCreate' => [
				new Response(200, [], $this->getFileContent('fixtures/CreateApplicationResponse.json'))
			],
			static::class . '::testCancel' => [
				new Response(200, [], $this->getFileContent('fixtures/CancelApplicationResponse.json'))
			],
			static::class . '::testChangeOrder' => [
				new Response(200, [], $this->getFileContent('fixtures/ChangeApplicationOrderResponse.json'))
			],
			static::class . '::testGetDetail' => [
				new Response(200, [], $this->getFileContent('fixtures/GetApplicationDetailResponse.json'))
			],
		];
		parent::setUp();
	}

	public function testMarkOrderAsSent()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = 'test-application-id';
		$applicationClient = new Application($httpClientFactory, new NullAuthorizationProcess());
		/** @var MarkOrderAsSentResponse $apiResponse */
		$apiResponse = $applicationClient->markOrderAsSent($applicationId);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/order/send', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\MarkOrderAsSentResponse', $apiResponse);
		$this->assertEquals('11800044', $apiResponse->getNumber());
	}

	public function testMarkOrderAsDelivered()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = 'test-application-id';
		$applicationClient = new Application($httpClientFactory, new NullAuthorizationProcess());
		/** @var MarkOrderAsDeliveredResponse $apiResponse */
		$apiResponse = $applicationClient->markOrderAsDelivered($applicationId);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/order/deliver', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse', $apiResponse);
		$this->assertEquals('0919160902', $apiResponse->getNumber());
	}

	public function testCreate()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);

		$applicationClient = new Application($httpClientFactory, new NullAuthorizationProcess());
		/** @var CreateApplicationRequest $applicationRequest */
		$applicationRequest = CreateApplicationRequest::fromArray(json_decode($this->getFileContent('fixtures/CreateApplicationRequest.json'), true));
		/** @var CreateApplicationResponse $apiResponse */
		$apiResponse = $applicationClient->create($applicationRequest);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CreateApplicationResponse', $apiResponse);
		$this->assertEquals('Ing. John Newborn, Csc.', $apiResponse->getCustomer()->getFullName());
	}

	public function testCancel()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = '01-11200a0ee1';
		$applicationClient = new Application($httpClientFactory, new NullAuthorizationProcess());
		/** @var CancelApplicationRequest $apiRequest */
		$apiRequest = CancelApplicationRequest::fromArray(json_decode($this->getFileContent('fixtures/CancelApplicationRequest.json'), true));
		/** @var CancelApplicationResponse $apiResponse */
		$apiResponse = $applicationClient->cancel($applicationId, $apiRequest);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/cancel', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CancelApplicationResponse', $apiResponse);
		$this->assertEquals($applicationId, $apiResponse->getId());
	}

	public function testChangeOrder()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = '01-11200a0ee1';
		$applicationClient = new Application($httpClientFactory, new NullAuthorizationProcess());
		/** @var ChangeApplicationOrderRequest $apiRequest */
		$apiRequest = ChangeApplicationOrderRequest::fromArray(json_decode($this->getFileContent('fixtures/ChangeApplicationOrderRequest.json'), true));
		/** @var ChangeApplicationOrderResponse $apiResponse */
		$apiResponse = $applicationClient->changeOrder($applicationId, $apiRequest);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/order', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse', $apiResponse);
		$this->assertEquals('AA234', $apiResponse->getNumber());
	}

	public function testGetDetail()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = '01-11200a0ee1';
		$applicationClient = new Application($httpClientFactory, new NullAuthorizationProcess());
		/** @var GetApplicationDetailResponse $apiResponse */
		$apiResponse = $applicationClient->getDetail($applicationId);

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId, (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse', $apiResponse);
		$this->assertEquals($applicationId, $apiResponse->getId());
	}
}
