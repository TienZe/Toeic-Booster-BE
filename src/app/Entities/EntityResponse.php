<?php

namespace App\Entities;

class EntityResponse extends Entity
{
    public function __construct(array|null $data = null, $success = true, $error = null)
    {
        $this->data = $data;
        $this->success = $success;
        $this->error = $error;
    }
}

