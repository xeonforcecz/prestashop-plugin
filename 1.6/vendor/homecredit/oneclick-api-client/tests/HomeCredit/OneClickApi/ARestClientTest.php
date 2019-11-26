<?php

namespace HomeCredit\OneClickApi;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class ARestClientTest extends TestCase
{

	const TEST_TYPE_LOCAL_MOCK = 0;
	const TEST_TYPE_APIARY_MOCK = 1;
	const TEST_TYPE_INTEGRATION = 2;

	protected static $testType;

	protected static $testVsMockMap = [];

	/**
	 * @param array $container
	 * @param MockHandler $mock
	 * @param LoggerInterface|null $logger
	 * @param string $baseUrl
	 * @return HttpClientFactory
	 */
	protected function getHttpClientFactory(
		&$container,
		MockHandler $mock = null,
		LoggerInterface $logger = null,
		$baseUrl = 'http://localhost/'
	)
	{
		$handlerStack = null;
		$history = Middleware::history($container);
		if ($mock) {
			$handlerStack = HandlerStack::create($mock);
		} else {
			$handlerStack = HandlerStack::create();
		}

		$handlerStack->push($history);
		$httpClientFactory = new HttpClientFactory([
			'baseUrl' => $baseUrl
		], $logger, $handlerStack);

		return $httpClientFactory;
	}

	/**
	 * @param mixed[] $container
	 * @param MockHandler $mock
	 * @param LoggerInterface|null $logger
	 * @return HttpClientFactory
	 */
	protected function getMockHttpClient(&$container, MockHandler $mock, LoggerInterface $logger = null)
	{
		return $this->getHttpClientFactory($container, $mock, $logger);
	}

	/**
	 * @param mixed[] $container
	 * @param LoggerInterface|null $logger
	 * @param string $baseUrl
	 * @return HttpClientFactory
	 */
	protected function getApiaryMockHttpClient(
		&$container,
		LoggerInterface $logger = null,
		$baseUrl = 'http://private-9698c-csoneclicknewfuture.apiary-mock.com/'
	)
	{
		return $this->getHttpClientFactory(
			$container,
			null,
			$logger,
			$baseUrl
		);
	}

	/**
	 * @param mixed[] $container
	 * @param LoggerInterface|null $logger
	 * @param string $baseUrl
	 * @return HttpClientFactory
	 */
	protected function getIntegrationHttpClient(
		&$container,
		LoggerInterface $logger = null,
		$baseUrl = 'https://apicz-test.homecredit.net/verdun-train/'
	)
	{
		return $this->getHttpClientFactory(
			$container,
			null,
			$logger,
			$baseUrl
		);
	}

	/**
	 * @param array $container
	 * @param string $testName
	 * @param MockHandler|null $mock
	 * @param LoggerInterface|null $logger
	 * @param string $baseUrl
	 * @return \HomeCredit\OneClickApi\HttpClientFactory
	 * @throws \Exception
	 */
	protected function getCurrentTestHttpClientFactory(
		&$container,
		$testName,
		MockHandler $mock = null,
		LoggerInterface $logger = null,
		$baseUrl = 'http://localhost/'
	)
	{
		if (self::$testType == self::TEST_TYPE_LOCAL_MOCK) {
			if (!isset(self::$testVsMockMap[$testName])) {
				throw new \Exception(sprintf('Mock is not set for %s', $testName));
			}
			return $this->getMockHttpClient($container, new MockHandler(self::$testVsMockMap[$testName]), $logger);
		} elseif (self::$testType == self::TEST_TYPE_APIARY_MOCK) {
			return $this->getApiaryMockHttpClient($container, $logger, $baseUrl);
		} elseif (self::$testType == self::TEST_TYPE_INTEGRATION) {
			return $this->getIntegrationHttpClient($container, $logger);
		}

		return $this->getHttpClientFactory($container, $mock, $logger, $baseUrl);
	}

	/**
	 * @param array $transaction
	 * @return Request
	 * @throws \Exception
	 */
	protected function getRequest(array $transaction)
	{
		if (!isset($transaction['request'])) {
			throw new \Exception('Request is not set');
		}

		return $transaction['request'];
	}

	/**
	 * @param array $transaction
	 * @return Response
	 * @throws \Exception
	 */
	protected function getResponse(array $transaction)
	{
		if (!isset($transaction['response'])) {
			throw new \Exception('Response is not set');
		}

		return $transaction['response'];
	}

	/**
	 * @param string $name
	 * @param string $logStream
	 * @return Logger
	 * @throws \Exception
	 */
	protected function getLogger($name = 'one-click-api', $logStream = 'php://stdout')
	{
		$loggger = new Logger($name);
		$loggger->pushHandler(new StreamHandler($logStream));

		return $loggger;
	}

	/**
	 * @param string $file
	 * @return string
	 * @throws \Exception
	 */
	protected function getFileContent($file)
	{
		$content = file_get_contents(__DIR__ . '/../../' . $file);
		if (!$content) {
			throw new \Exception(sprintf('File %s has not been loaded', $file));
		}

		return $content;
	}

}
