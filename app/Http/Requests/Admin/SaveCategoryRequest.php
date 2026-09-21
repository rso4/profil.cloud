<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveCategoryRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ];
    }
}
