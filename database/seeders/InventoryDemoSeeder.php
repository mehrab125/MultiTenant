<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (){
           $face = fake();
           $now = now();

           for ($t=0;$t <=5;$t++){
               $tenant_id = DB::table('tenants')->insertGetId([
                  'name'=>"Company {$t}",
                   'subdomain'=> "SubDomain-{$t}",
                   'created_at' => $now,
               ]);

               $warehouses = [];
               for ($w=0;$w <= 3;$w++){
                   $warehouses[] = DB::table('warehouses')->insertGetId([
                      'tenant_id'=>$tenant_id,
                       'name'=>"WearHouse {$w}",
                       'location'=>$face->city(),
                       'created_at' => $now,
                   ]);
               }

               for ($p=0;$p <= 15;$p++){
                   $productId = DB::table('products')->insertGetId([
                        'tenant_id'=>$tenant_id,
                       'sku'=>'SKU-'.$p,
                       'name'=>$face->word(5),
                       'unit_price'=>$face->randomFloat(2,100,10000),
                       'created_at' => $now,
                   ]);
               }


               [$source, $destination, $third] = $warehouses;
               $movements = [
                   [
                       'warehouse_id' => $source,
                       'type' => 'in',
                       'quantity' => 50,
                       'reference' => "IN-{$productId}-1",
                       'meta' => null,
                   ],
                   [
                       'warehouse_id' => $destination,
                       'type' => 'in',
                       'quantity' => 20,
                       'reference' => "IN-{$productId}-2",
                       'meta' => null,
                   ],
                   [
                       'warehouse_id' => $source,
                       'type' => 'out',
                       'quantity' => 5,
                       'reference' => "OUT-{$productId}",
                       'meta' => null,
                   ],
                   [
                       'warehouse_id' => $source,
                       'type' => 'transfer',
                       'quantity' => 10,
                       'reference' => "TRF-{$productId}",
                       'meta' => json_encode([
                           'from_warehouse_id' => $source,
                           'to_warehouse_id' => $destination,
                       ]),
                   ],
               ];

               foreach ($movements as $movement) {
                   DB::table('stock_movements')->insert([
                       'tenant_id' => $tenant_id,
                       'product_id' => $productId,
                       'warehouse_id' => $movement['warehouse_id'],
                       'type' => $movement['type'],
                       'quantity' => $movement['quantity'],
                       'reference' => $movement['reference'],
                       'meta' => $movement['meta'],
                       'created_at' => $now,
                   ]);
               }

               foreach ([$source => 35, $destination => 30, $third => 0] as $warehouseId => $quantity) {

                   DB::table('stock_levels')->insert([
                       'tenant_id' => $tenant_id,
                       'product_id' => $productId,
                       'warehouse_id' => $warehouseId,
                       'quantity' => $quantity,
                       'updated_at' => $now,
                   ]);
               }

           }
        });
    }
}
