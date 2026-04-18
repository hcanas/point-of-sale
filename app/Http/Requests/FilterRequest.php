<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class FilterRequest extends FormRequest
{
    abstract protected function allowedSorts(): array;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'sort' => [
                'nullable',
                'string',
                Rule::in($this->allowedSorts()),
            ],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function sortColumn(): string
    {
        $sort = $this->validated('sort');

        return in_array($sort, $this->allowedSorts(), true) ? $sort : $this->defaultSort();
    }

    public function sortDirection(): string
    {
        $direction = $this->validated('sort_direction');

        return in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
    }

    protected function defaultSort(): string
    {
        return $this->allowedSorts()[0] ?? 'id';
    }
}
