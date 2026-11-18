<?php

namespace App\Http\Requests;

use App\Services\Tenant\TenantGlobalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexStockLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant_id = app(TenantGlobalService::class)->get();

        return [
            'product_id' => [
                'sometimes', 'integer', Rule::exists('products', 'id')->where('tenant_id', $tenant_id),
            ],
            'warehouse_id' => [
                'sometimes', 'integer', Rule::exists('warehouses', 'id')->where('tenant_id', $tenant_id),
            ],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
