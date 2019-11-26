<?php

namespace RoundingWell\Schematic;

abstract class Schema
{
    const SCHEMA_TYPES = [
        'array',
        'boolean',
        'integer',
        'null',
        'number',
        'object',
        'string'
    ];

    /**
     * @param string $path
     * @return static
     */
    public static function fromFile($path)
    {
        return self::make(json_decode(file_get_contents($path)));
    }

    /**
     * @param object $json
     * @return mixed
     */
    public static function make($json)
    {
        if (!isset($json->type)) {
            throw new \InvalidArgumentException('Missing schema type.');
        }

        if (!in_array(strtolower($json->type), self::SCHEMA_TYPES)) {
            throw new \InvalidArgumentException(sprintf(
                'No schema type available for %s.',
                $json->type
            ));
        }

        $schema = 'RoundingWell\\Schematic\\Schema\\' . ucfirst($json->type) . 'Schema';

        return new $schema($json);
    }

    /**
     * @var object
     */
    protected $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function type()
    {
        return $this->schema->type;
    }

    abstract public function phpType();

    public function isArray()
    {
        return $this->type() === 'array';
    }

    public function isBoolean()
    {
        return $this->type() === 'boolean';
    }

    public function isInteger()
    {
        return $this->type() === 'integer';
    }

    public function isNull()
    {
        return $this->type() === 'null';
    }

    public function isNumber()
    {
        return $this->type() === 'number';
    }

    public function isObject()
    {
        return $this->type() === 'object';
    }

    public function isString()
    {
        return $this->type() === 'string';
    }

    public function title()
    {
        return $this->schema->title ? $this->schema->title : '';
    }

    public function getDescription()
    {
        return $this->schema->description ? $this->schema->description : '';
    }

    public function isEnum()
    {
        return isset($this->schema->enum);
    }

    public function getEnum()
    {
        return isset($this->schema->enum) ? $this->schema->enum : [];
    }

}
