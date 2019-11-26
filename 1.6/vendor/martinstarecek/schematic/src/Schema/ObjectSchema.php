<?php

namespace RoundingWell\Schematic\Schema;

use RoundingWell\Schematic\Schema;

class ObjectSchema extends Schema
{
    public function phpType()
    {
        return 'object';
    }

    /**
     * @return Schema[]
     */
    public function properties()
    {
        $props = [];

        foreach ($this->schema->properties as $name => $schema) {
            $props[$name] = Schema::make($schema);
        }

        foreach (isset($this->schema->oneOf) ? $this->schema->oneOf : [] as $oneOf) {
            foreach ($oneOf->properties as $name => $schema) {
                $props[$name] = Schema::make($schema);
            }
        }

        return $props;
    }

    /**
     * @return string[]
     */
    public function required()
    {
        return !is_null($this->schema->required) ? $this->schema->required : [];
    }

    public function isRequired($property)
    {
        return in_array($property, $this->required());
    }
}
