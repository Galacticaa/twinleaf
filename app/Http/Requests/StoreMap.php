<?php

namespace Twinleaf\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMap extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $unique = !$this->map ? 'unique:maps'
                : 'unique:maps,id,'.$this->map->id;

        return [
            'name' => 'required',
            'code' => 'required|'.$unique,
            'url' => 'required',
            'description' => 'required|string',
            'image_url' => 'required|string',
            'location' => 'required',
            'db_name' => 'required',
            'db_user' => 'required',
            'db_pass' => 'required',
        ];
    }
}
