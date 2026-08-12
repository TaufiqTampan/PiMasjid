<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZakatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'muzakki_name' => 'required|string|max:255',
            'muzakki_nik' => 'nullable|string|max:20',
            'muzakki_phone' => 'nullable|string|max:20',
            'muzakki_address' => 'nullable|string',
            'type' => 'required|in:fitrah,mal,profesi',
            'amount' => 'required|numeric|min:0',
            'payment_type' => 'required|in:uang,beras',
            'rice_kg' => 'nullable|numeric|min:0',
            'person_count' => 'nullable|integer|min:1',
            'year' => 'required|integer',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }
}
