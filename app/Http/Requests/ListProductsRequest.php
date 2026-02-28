<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ListProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public endpoint – no auth required
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'categoryId'         => ['sometimes', 'string', 'uuid'],
            'companyId'          => ['sometimes', 'string', 'uuid'],
            'q'                  => ['sometimes', 'string', 'max:100'],
            'minPrice'           => ['sometimes', 'integer', 'min:0'],
            'maxPrice'           => ['sometimes', 'integer', 'min:0', 'gte:minPrice'],
            'sortBy'             => ['sometimes', 'string', 'in:name,price'],
            'sortDir'            => ['sometimes', 'string', 'in:asc,desc'],
            'perPage'            => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page'               => ['sometimes', 'integer', 'min:1'],
            'onlyWithPromotion'  => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'maxPrice.gte'  => 'The maxPrice must be greater than or equal to minPrice.',
            'sortBy.in'     => 'sortBy must be one of: name, price.',
            'sortDir.in'    => 'sortDir must be one of: asc, desc.',
        ];
    }
}
