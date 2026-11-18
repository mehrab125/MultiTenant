<?php

namespace App\Console\Commands;

use App\Models\ProductsModel;
use App\Models\WarehousesModel;
use App\Models\StockLevelsModel;
use App\Services\Inventory\InventoryService;
use App\Services\Tenant\TenantGlobalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Str;
use DomainException;
use InvalidArgumentException;
use Throwable;

class TestStockConcurrency extends Command
{
    protected $signature = 'inventory:test {tenant=1}';

    public function handle(): int
    {
        $tenant_id = (int) $this->argument('tenant');

        app(TenantGlobalService::class)->set($tenant_id);

        $warehouse = WarehousesModel::query()->firstOrFail();

        $product = ProductsModel::create([
            'sku' => 'TEST-' . Str::random(10),
            'name' => 'Test Product',
            'unit_price' => 1000,
        ]);

        $inventory = app(InventoryService::class);

        $inventory->StockIn($product->id, $warehouse->id, 50);

        $tasks = [];

        for ($i = 0; $i < 100; $i++) {
            $tasks[] = function () use ($tenant_id, $inventory, $product, $warehouse) {
                app(TenantGlobalService::class)->set($tenant_id);
                try {
                    $inventory->StockOut($product->id, $warehouse->id, 1);
                    return true;
                } catch (DomainException) {
                    return false;
                } catch (InvalidArgumentException $e) {
                    return 'invalid: ' . $e->getMessage();
                } catch (Throwable $e) {
                    return 'error: ' . $e->getMessage();
                }
            };
        }

        try {
            $results = Concurrency::driver('process')->run($tasks, timeout: 120);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $success = count(array_filter($results));
        $failed = count($results) - $success;

        $this->info("Success: ".$success);
        $this->warn("Failed: ".$failed);
        $this->error("---------------------");

        if ($success == 50 && $failed == 50){
            $this->info('Success Task');
            return self::SUCCESS;
        }else{
            $this->error('Failed Task');
            return self::FAILURE;
        }
    }
}
