<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kategori = $this->route('kategori');
        
        return [
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori', 'nama_kategori')->ignore($kategori),
            ],
        ];
    }
}