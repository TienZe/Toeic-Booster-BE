<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRatingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'collection_id' => 'required|integer|exists:collections,id',
            'rate' => 'required|numeric|min:1|max:5',
            'personal_message' => 'nullable|string|max:255',
        ];
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['collection_id'] = $this->route('collectionId');
        return $data;
    }
}
