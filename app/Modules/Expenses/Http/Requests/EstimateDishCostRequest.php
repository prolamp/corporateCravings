<?php

namespace App\Modules\Expenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstimateDishCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cost_calculator.view') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'yield_portions' => ['required', 'numeric', 'min:0.01'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0'],
        ];
    }
}
