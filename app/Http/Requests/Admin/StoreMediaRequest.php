<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tenant = current_tenant();

        return $tenant !== null && $this->user() !== null
            && $this->user()->tenant_id === $tenant->id;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
            'alt_text' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File gambar wajib dipilih.',
            'file.image' => 'File harus berupa gambar (JPEG/PNG/WebP/GIF).',
            'file.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
