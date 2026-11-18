<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Services\Inventory\InventoryService;
use DomainException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $request, InventoryService $inventory): JsonResponse {
        $data = $request->validated();

        try {
            $movement = match ($data['type']) {
                'in' => $inventory->StockIn(
                    $data['product_id'],
                    $data['warehouse_id'],
                    $data['quantity'],
                    $data['reference'] ?? null
                ),
                'out' => $inventory->StockOut(
                    $data['product_id'],
                    $data['warehouse_id'],
                    $data['quantity'],
                    $data['reference'] ?? null
                ),
                'transfer' => $inventory->Transfer(
                    $data['product_id'],
                    $data['from_warehouse_id'],
                    $data['to_warehouse_id'],
                    $data['quantity'],
                    $data['reference'] ?? null
                ),
            };

            return (new StockMovementResource($movement))->response()->setStatusCode(201);

        } catch (DomainException | InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
