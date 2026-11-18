<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('stock_movements')]
#[Fillable(['product_id','warehouse_id','type','quantity','reference','meta'])]
class StockMovementsModel extends Model
{
    use BelongsToTenant;
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
          'meta'=>'array'
        ];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductsModel::class,'product_id','id');
    }
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WarehousesModel::class,'warehouse_id','id');
    }

}
