<?php

namespace RoundingWell\Schematic\Schema;

use RoundingWell\Schematic\Schema;

class ArraySchema extends Schema
{
    public function phpType()
    {
        if ($this->hasItems()) {
            return $this->items()->phpType() . '[]';
        }

        return 'array';
    }

    public function hasItems()
    {
        return isset($this->schema->items)
            && isset($this->schema->items->type);
    }

    public function items()
    {
        return $this->hasItems() ? Schema::make($this->schema->items) : null;
    }
}
