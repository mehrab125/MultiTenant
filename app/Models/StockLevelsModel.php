<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('stock_levels')]
#[Fillable(['product_id','warehouse_id','quantity'])]
class StockLevelsModel extends Model
{
    use BelongsToTenant;
    public const CREATED_AT = null;

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductsModel::class,'product_id','id');
    }
    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WarehousesModel::class,'warehouse_id','id');
    }
}
