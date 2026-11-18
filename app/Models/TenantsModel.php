<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('tenants')]
#[Fillable(['name','subdomain'])]
class TenantsModel extends Model
{
    public const UPDATED_AT = null;

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductsModel::class,'tenant_id','id');
    }
    public function warehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WarehousesModel::class,'tenant_id','id');
    }

}
