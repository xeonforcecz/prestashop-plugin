<?php

namespace HomeCredit\OneClickApi\RestClient;

use GuzzleHttp\Exception\ClientException;
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
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\NullAuthorizationProcess;
use HomeCredit\OneClickApi\ARestClientTest;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class ApplicationIntegrationTest
 * @package HomeCredit\OneClickApi\RestClient
 * @group Int
 */
class ApplicationIntegrationTest extends ARestClientTest
{

	protected function setUp()
	{
		self::$testType = self::TEST_TYPE_INTEGRATION;
		parent::setUp();
	}

	public function testCreate()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);

		$applicationClient = new Application($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var CreateApplicationRequest $applicationRequest */
		$applicationRequest = CreateApplicationRequest::fromArray(json_decode($this->getFileContent('fixtures/CreateApplicationRequest.json'), true));
		$applicationRequest->getOrder()->setNumber(uniqid('', true));
		/** @var CreateApplicationResponse $apiResponse */
		$apiResponse = $applicationClient->create($applicationRequest);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CreateApplicationResponse', $apiResponse);
		$this->assertTrue($apiResponse->getState() == CreateApplicationResponse::STATE_PROCESSING);

		return $apiResponse;
	}

	/**
	 * @depends testCreate
	 * @param CreateApplicationResponse $applicationResponse
	 * @throws \Nette\Utils\JsonException
	 */
	public function testGetDetail(CreateApplicationResponse $applicationResponse)
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = $applicationResponse->getId();
		$applicationClient = new Application($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var GetApplicationDetailResponse $apiResponse */
		$apiResponse = $applicationClient->getDetail($applicationId);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId, (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\GetApplicationDetailResponse', $apiResponse);
	}

	/**
	 * @depends testCreate
	 * @param CreateApplicationResponse $applicationResponse
	 * @throws \Nette\Utils\JsonException
	 */
	public function testChangeOrder(CreateApplicationResponse $applicationResponse)
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = $applicationResponse->getId();
		$applicationClient = new Application($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var ChangeApplicationOrderRequest $apiRequest */
		$apiRequest = ChangeApplicationOrderRequest::fromArray(json_decode($this->getFileContent('fixtures/ChangeApplicationOrderRequest.json'), true));
		/** @var ChangeApplicationOrderResponse $apiResponse */
		$apiResponse = $applicationClient->changeOrder($applicationId, $apiRequest);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/order', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\ChangeApplicationOrderResponse', $apiResponse);
	}

	/**
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
//	public function testMarkOrderAsSent()
//	{
//		$container = [];
//		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
//		$applicationId = '01-aceba268-8093-4a28-a344-8bfbad7a284c';
//		$applicationClient = new Application($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
//		/** @var MarkOrderAsSentResponse $apiResponse */
//		$apiResponse = $applicationClient->markOrderAsSent($applicationId);
//		$this->assertCount(2, $container);
//		$transaction = array_pop($container);
//		$request = $this->getRequest($transaction);
//		$this->assertContains('financing/v1/applications/' . $applicationId . '/order/send', (string) $request->getUri());
//		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\MarkOrderAsSentResponse', $apiResponse);
//	}

	/**
	 * @throws \Nette\Utils\JsonException
	 * @throws \Exception
	 */
	public function testMarkOrderAsDelivered()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = '01-b0a8e326-7ea2-401c-bfd5-514553290b17';
		$applicationClient = new Application($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var MarkOrderAsDeliveredResponse $apiResponse */
		$apiResponse = $applicationClient->markOrderAsDelivered($applicationId);
		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/order/deliver', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse', $apiResponse);
	}

	/**
	 * @depends testCreate
	 * @param CreateApplicationResponse $applicationResponse
	 * @throws \Nette\Utils\JsonException
	 */
	public function testCancel(CreateApplicationResponse $applicationResponse)
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$applicationId = $applicationResponse->getId();
		$applicationClient = new Application($httpClientFactory, new AuthTokenAuthorizationProcess('024242tech', '024242tech'));
		/** @var CancelApplicationRequest $apiRequest */
		$apiRequest = CancelApplicationRequest::fromArray(json_decode($this->getFileContent('fixtures/CancelApplicationRequest.json'), true));
		/** @var CancelApplicationResponse $apiResponse */
		$apiResponse = $applicationClient->cancel($applicationId, $apiRequest);

		$this->assertCount(2, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('financing/v1/applications/' . $applicationId . '/cancel', (string) $request->getUri());
		$this->assertInstanceOf('HomeCredit\OneClickApi\Entity\CancelApplicationResponse', $apiResponse);
	}

}
