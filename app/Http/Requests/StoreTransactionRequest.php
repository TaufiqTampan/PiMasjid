<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date'],
            'proof_image' => ['required_if:type,expense', 'nullable', 'image', 'mimes:jpg,jpeg,png,pdf', 'max:'.(5 * 1024)], // 5MB max
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'type' => 'tipe transaksi',
            'category' => 'kategori',
            'amount' => 'jumlah',
            'description' => 'keterangan',
            'date' => 'tanggal',
            'proof_image' => 'bukti transaksi',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'proof_image.required_if' => 'Bukti transaksi wajib diupload untuk pengeluaran.',
            'proof_image.image' => 'File harus berupa gambar.',
            'proof_image.mimes' => 'Format file harus: JPG, JPEG, PNG, atau PDF.',
            'proof_image.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
