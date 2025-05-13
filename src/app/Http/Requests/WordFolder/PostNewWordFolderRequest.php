<?php

namespace App\Http\Requests\WordFolder;

use Illuminate\Foundation\Http\FormRequest;

class PostNewWordFolderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string|max:255',
        ];
    }
}
