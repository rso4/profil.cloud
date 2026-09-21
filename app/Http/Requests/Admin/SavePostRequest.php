<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePostRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'excerpt' => 'nullable|string|max:1000',
            'content' => 'nullable|string|max:200000',
            'category_id' => 'nullable|integer',
            'cover_media_id' => 'nullable|integer',
            'tags' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in(['draft', 'published', 'scheduled'])],
            'published_at' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('status') === 'scheduled'),
            ],
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'published_at.required' => 'Tanggal tayang wajib diisi untuk artikel terjadwal.',
        ];
    }
}
