<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexStockLevelRequest;
use App\Http\Resources\StockLevelResource;
use App\Http\Resources\StockMovementResource;
use App\Models\StockLevelsModel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class StockLevelController extends Controller
{
    public function index(IndexStockLevelRequest $request): AnonymousResourceCollection | response {
        $data = $request->validated();

        $stocks = StockLevelsModel::query()->when(
            isset($data['product_id']),
                fn ($query) => $query->where('product_id', $data['product_id'])
            )->when(
                isset($data['warehouse_id']),
                fn ($query) => $query->where('warehouse_id', $data['warehouse_id'])
            )
            ->orderBy('id')
            ->paginate($data['per_page'] ?? 20)->withQueryString();;

        if ($stocks->isNotEmpty()){
            return StockLevelResource::collection($stocks);
        }else{
            return response(['message'=>__('alert.fieldNotFound',['field'=>'Stock'])],404);
        }
    }
}
