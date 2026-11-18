<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('warehouses')]
#[Fillable(['name','location'])]
class WarehousesModel extends Model
{
    use BelongsToTenant;
    public const UPDATED_AT = null;
    public function stock_movments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMovementsModel::class,'warehouse_id','id');
    }

    public function stock_levels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockLevelsModel::class,'warehouse_id','id');
    }

}
