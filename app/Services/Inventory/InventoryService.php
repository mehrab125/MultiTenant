<?php
namespace App\Services\Inventory;
use App\Models\ProductsModel;
use DomainException;
use InvalidArgumentException;

class InventoryService{
    private bool $createStockMovements=true;
    public function StockOut(
        int $product_id,
        int $warehouse_id,
        int $quantity,
        ?string $reference = null
    ):  ?\App\Models\StockMovementsModel
    {
        if (!\App\Models\ProductsModel::where('id',$product_id)->exists()){
            throw new InvalidArgumentException(__('alert.fieldNotFound',['field'=>'Product']));
        }

        if (!\App\Models\WarehousesModel::where('id',$warehouse_id)->exists()){
            throw new InvalidArgumentException(__('alert.fieldNotFound',['field'=>'Warehouse']));
        }

        if ($quantity <= 0){
            throw new InvalidArgumentException(__('alert.fieldNotFound',['field'=>'Quantity']));
        }

        $tenant_id = app(\App\Services\Tenant\TenantGlobalService::class)->get();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($product_id,$warehouse_id,$quantity,$reference) {
            ProductsModel::query()->whereKey($product_id)->lockForUpdate()->firstOrFail();

            $stock = \App\Models\StockLevelsModel::query()->where('product_id',$product_id)->where('warehouse_id',$warehouse_id)->lockForUpdate()->first();
            if (!$stock || $stock->quantity < $quantity){
                throw new DomainException(__('alert.stockAlert',['field'=>'Insufficient']));
            }

            $stock->decrement('quantity',$quantity);

            if ($this->createStockMovements){
               return \App\Models\StockMovementsModel::create([
                    'product_id'=>$product_id,
                    'warehouse_id'=>$warehouse_id,
                    'type'=>'out',
                    'quantity'=>$quantity,
                    'reference'=>$reference
                ]);
            }
        },3);
    }

    public function StockIn(
        int $product_id,
        int $warehouse_id,
        int $quantity,
        ?string $reference = null
    ):  ?\App\Models\StockMovementsModel
    {
        if (!\App\Models\ProductsModel::where('id',$product_id)->exists()){
            throw new InvalidArgumentException(__('alert.fieldNotFound',['field'=>'Product']));
        }

        if (!\App\Models\WarehousesModel::where('id',$warehouse_id)->exists()){
            throw new InvalidArgumentException(__('alert.fieldNotFound',['field'=>'Warehouse']));
        }

        if ($quantity <= 0){
            throw new InvalidArgumentException(__('alert.fieldNotFound',['field'=>'Quantity']));
        }

       return \Illuminate\Support\Facades\DB::transaction(function () use ($product_id,$warehouse_id,$quantity,$reference) {
            ProductsModel::query()->whereKey($product_id)->lockForUpdate()->firstOrFail();

            $stock = \App\Models\StockLevelsModel::query()->where('product_id',$product_id)->where('warehouse_id',$warehouse_id)->lockForUpdate()->first();
            if ($stock) {
                $stock->increment('quantity',$quantity);
            }else{
                \App\Models\StockLevelsModel::create([
                    'product_id'=>$product_id,
                    'warehouse_id'=>$warehouse_id,
                    'quantity' => $quantity,
                ]) ;
            }
            if ($this->createStockMovements){
               return \App\Models\StockMovementsModel::create([
                    'product_id'=>$product_id,
                    'warehouse_id'=>$warehouse_id,
                    'type'=>'in',
                    'quantity'=>$quantity,
                    'reference'=>$reference
                ]);
            }
        },3);
    }


    public function Transfer(
        int $product_id,
        int $from_warehouse_id,
        int $to_warehouse_id,
        int $quantity,
        ?string $reference = null
    ):  ?\App\Models\StockMovementsModel
    {
        if ($from_warehouse_id == $to_warehouse_id) {
            throw new DomainException(__('alert.warehousesSame'));
        }

        $this->createStockMovements = false;
        try {
           return  \Illuminate\Support\Facades\DB::transaction(function () use ($product_id, $from_warehouse_id, $to_warehouse_id, $quantity, $reference) {
                ProductsModel::query()->whereKey($product_id)->lockForUpdate()->firstOrFail();

                $this->StockOut($product_id, $from_warehouse_id, $quantity);
                $this->StockIn($product_id, $to_warehouse_id, $quantity);

               return \App\Models\StockMovementsModel::create([
                    'product_id' => $product_id,
                    'warehouse_id' => $from_warehouse_id,
                    'type' => 'transfer',
                    'quantity' => $quantity,
                    'reference' => $reference,
                    'meta' => [
                        'from_warehouse_id' => $from_warehouse_id,
                        'to_warehouse_id' => $to_warehouse_id,
                    ],
                ]);
            }, 3);
        } finally {
            $this->createStockMovements=true;
        }
    }
}
