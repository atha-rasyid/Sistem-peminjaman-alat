<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof \App\Models\User ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($userId),
        ],
          'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()
        ],
          'role' => ['required',
             Rule::in(['admin', 'petugas', 'peminjam']),
        ],
          'no_hp' =>['nullable', 'string', 'max:15'],
          'alamat' => ['nullable', 'string'],
          'foto_profil' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }
}