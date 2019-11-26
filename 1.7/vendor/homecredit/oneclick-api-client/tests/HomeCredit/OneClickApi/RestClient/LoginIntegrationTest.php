<?php

namespace HomeCredit\OneClickApi\RestClient;

use GuzzleHttp6\Handler\MockHandler;
use GuzzleHttp6\Psr7\Response;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramDetailResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsDownPaymentsResponse;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferRequest;
use HomeCredit\OneClickApi\Entity\CalculateInstallmentProgramsOfferResponse;
use HomeCredit\OneClickApi\RestClient;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess;
use HomeCredit\OneClickApi\RestClient\AuthorizationProcess\NullAuthorizationProcess;
use HomeCredit\OneClickApi\ARestClientTest;
use Psr\Log\LoggerInterface;

/**
 * Class LoginIntegrationTest
 * @package HomeCredit\OneClickApi\RestClient
 * @group Int
 */
class LoginIntegrationTest extends ARestClientTest
{

	protected function setUp()
	{
		self::$testType = self::TEST_TYPE_INTEGRATION;
		parent::setUp();
	}

	public function testLogin()
	{
		$container = [];
		$httpClientFactory = $this->getCurrentTestHttpClientFactory($container, __METHOD__);
		$authProcess = new AuthTokenAuthorizationProcess('024242tech', '024242tech');
		$apiClient = new RestClient($httpClientFactory, $authProcess);
		$apiClient->login();

		$this->assertCount(1, $container);
		$transaction = array_pop($container);
		$request = $this->getRequest($transaction);
		$this->assertContains('authentication/v1/partner', (string) $request->getUri());
		$this->assertTrue($authProcess->isLoggedIn() === true);
	}

}
