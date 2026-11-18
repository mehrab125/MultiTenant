<?php

namespace App\Http\Requests;

use App\Services\Tenant\TenantGlobalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant_id = app(TenantGlobalService::class)->get();

        $warehouseRule = Rule::exists('warehouses', 'id')->where('tenant_id', $tenant_id);

        return [
            'type' => [
                'required', Rule::in(['in', 'out', 'transfer']),
            ],
            'product_id' => [
                'required', 'integer', Rule::exists('products', 'id')->where('tenant_id', $tenant_id),
            ],
            'warehouse_id' => [
                'required_unless:type,transfer', 'prohibited_if:type,transfer', 'integer', $warehouseRule,
            ],
            'from_warehouse_id' => [
                'required_if:type,transfer', 'prohibited_unless:type,transfer', 'integer', $warehouseRule,
            ],
            'to_warehouse_id' => [
                'required_if:type,transfer', 'prohibited_unless:type,transfer', 'integer', 'different:from_warehouse_id', $warehouseRule,
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
