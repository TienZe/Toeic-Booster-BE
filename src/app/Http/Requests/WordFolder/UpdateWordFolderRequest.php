<?php

namespace App\Http\Requests\WordFolder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWordFolderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:255',
        ];
    }
}
