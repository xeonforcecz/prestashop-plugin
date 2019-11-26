<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'vendor/autoload.php';

foreach (glob(__DIR__ . '/shared/json-schemas/*.json') as $schemaFile) {
	try {
		Nette\Utils\Json::encode(file_get_contents($schemaFile));
		$schema = \RoundingWell\Schematic\Schema\ObjectSchema::fromFile($schemaFile);
	} catch (Exception $e) {
		echo $e->getMessage() . $schemaFile;
		continue;
	}

	$generator = new \HomeCredit\OneClickApi\EntityGenerator\Generator();
	$classes = $generator->generate($schema, 'HomeCredit\OneClickApi\Entity\\' . pathinfo(basename($schemaFile), PATHINFO_FILENAME), 'AEntity');
	foreach ($classes as $class) {
		$class->saveToFs(__DIR__ . '/src');
	}
}
