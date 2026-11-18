<?php
namespace App\Traits;

use App\Models\TenantsModel;
use App\Scope\Tenant\TenantGlobalScope;
use App\Services\Tenant\TenantGlobalService;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\Exception\LogicException;

trait BelongsToTenant{

    protected static function booted(): void
    {
        self::addGlobalScope(new TenantGlobalScope());

        self::creating(function (Model $model){
            $tenant_id = app(TenantGlobalService::class)->get();
            if ($model->getAttribute('tenant_id') !== null && $model->getAttribute('tenant_id') !== $tenant_id){
                throw new LogicException(__('alert.fieldMismatch',['field'=>'tenant_id']));
            }

            $model->setAttribute('tenant_id',$tenant_id);
        });
    }

    public function tenant() : \Illuminate\Database\Eloquent\Relations\BelongsTo{
        return $this->belongsTo(TenantsModel::class,'tenant_id','id');
    }
}
