<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('products')]
#[Fillable(['sku','name','unit_price'])]
class ProductsModel extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    public function stock_movments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovementsModel::class,'product_id','id');
    }

    public function stock_levels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockLevelsModel::class,'product_id','id');
    }

}
