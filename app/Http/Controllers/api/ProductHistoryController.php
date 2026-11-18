<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductHistoryRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\ProductsModel;
use App\Models\StockMovementsModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductHistoryController extends Controller
{
    public function index(ProductHistoryRequest $request, string $sku): AnonymousResourceCollection | response {
        $data = $request->validated();


        $product = ProductsModel::query()->where('sku', $sku)->get();

        if ($product->isNotEmpty()){
        $movements = StockMovementsModel::query()
            ->where('product_id', $product->first()->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($data['per_page'] ?? 20)
            ->withQueryString();

            return StockMovementResource::collection($movements);
        }else{
            return response(['message'=>__('alert.fieldNotFound',['field'=>'Stock Movements'])],404);
        }
    }
}
