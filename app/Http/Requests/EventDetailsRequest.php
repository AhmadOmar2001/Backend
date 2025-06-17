<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventDetailsRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'hall' => 'required|exists:halls,id',
            'seats_number' => 'required|integer',
            'description' => 'required',
        ];
    }
}