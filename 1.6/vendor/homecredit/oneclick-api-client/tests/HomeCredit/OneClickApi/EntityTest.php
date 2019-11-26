<?php

namespace HomeCredit\OneClickApi;

use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityTest
 * @package HomeCredit\OneClickApi
 * @group Unit
 */
class EntityTest extends TestCase
{

	/**
	 * @dataProvider jsonFilesMapDataProvider
	 * @param string $from
	 * @param string $className
	 * @throws \Nette\Utils\JsonException
	 */
	public function testCreateEntity($from, $className)
	{
		$from = file_get_contents($from);
		Json::encode($from); //
		$applicationRequest = $className::fromArray(json_decode($from, true));
		$to = json_encode($applicationRequest, JSON_PRETTY_PRINT);
		$this->assertJsonStringEqualsJsonString($from, $to);
	}

	/**
	 * @dataProvider jsonFilesMapDataProvider
	 * @param string $from
	 * @param string $className
	 * @throws \Nette\Utils\JsonException
	 */
	public function testConvertToJson($from, $className)
	{
		$from = file_get_contents($from);
		Json::encode($from); //
		$applicationRequest = $className::fromArray(json_decode($from, true));
		$to = json_encode($applicationRequest, JSON_PRETTY_PRINT);
		$this->assertJsonStringEqualsJsonString($from, $to);
	}

	/**
	 * @return string[]
	 */
	public function jsonFilesMapDataProvider()
	{
		$names = [
			'CreateApplicationRequest',
			'CreateApplicationResponse',
			'GetApplicationDetailResponse',
			'CancelApplicationRequest',
			'CancelApplicationResponse',
			'ChangeApplicationOrderRequest',
			'ChangeApplicationOrderResponse',
			'MarkOrderAsSentResponse',
			'MarkOrderAsDeliveredResponse',
			'GetOrderResponse',
			'CalculateInstallmentProgramsOfferRequest',
			'CalculateInstallmentProgramsOfferResponse',
		];
		$map = [];
		foreach ($names as $name) {
			array_push($map, [
				__DIR__ . '/../../fixtures/' . $name . '.json',
				'HomeCredit\OneClickApi\Entity\\' . $name
			]);
		}

		return $map;
	}

}
