<?php

namespace App\Modules\Cms\Http\Requests;

use App\Models\Page;
use App\Support\StatusRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Page $page */
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('pages', 'slug')->ignore($page->id),
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'body' => ['nullable', 'string', 'max:100000'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(StatusRegistry::codes('page'))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        if (is_string($slug) && trim($slug) !== '') {
            $slug = \Illuminate\Support\Str::slug(trim($slug)) ?: null;
        } else {
            $slug = null;
        }

        $this->merge([
            'slug' => $slug,
            'body' => $this->filled('body') ? $this->input('body') : null,
            'meta_title' => $this->filled('meta_title') ? $this->input('meta_title') : null,
            'meta_description' => $this->filled('meta_description') ? $this->input('meta_description') : null,
        ]);
    }
}
