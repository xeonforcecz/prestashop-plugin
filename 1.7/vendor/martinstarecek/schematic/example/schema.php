<?php

require __DIR__ . '/../vendor/autoload.php';

use RoundingWell\Schematic\Schema;

/** @var Schema\ObjectSchema $schema */
$schema = Schema::fromFile(__DIR__ . '/person.json');

echo $schema->title(), "\n";
foreach ($schema->properties() as $name => $schema) {
    echo " - $name ", $schema->phpType(), "\n";
    if ($schema->isArray()) {
        /** @var Schema\ArraySchema $schema */
        if ($schema->items()->isObject()) {
            foreach ($schema->items()->properties() as $innerName => $innerSchema) {
                echo "   - $name ", $schema->phpType(), "\n";
            }
        }
    } elseif ($schema->isObject()) {
        foreach ($schema->properties() as $propName => $propValue) {
            echo "   - $name ", $schema->phpType(), "\n";
        }
    }
}
