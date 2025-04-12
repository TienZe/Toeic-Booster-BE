<?php

namespace App\Entities;

class Entity implements \JsonSerializable
{
    public function toArray()
    {
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $fields = [];
        foreach ($properties as $property) {
            $name    = $property->getName();
            $value   = $property->getValue($this);
            $fields[$name] = $value;
        }
        ksort($fields);
        return $fields;
    }


    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
