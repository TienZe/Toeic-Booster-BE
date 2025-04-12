<?php

namespace App\Http\Resources;

use App\Entities\EntityResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse extends JsonResource
{
    public static function respond(array|null $data, bool $success = true, $error = null)
    {
        return new self(new EntityResponse($data, $success, $error));
    }
}
