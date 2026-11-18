<?php
namespace App\Scope\Tenant;

use App\Services\Tenant\TenantGlobalService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantGlobalScope implements Scope {

    public function apply(Builder $builder, Model $model) : void
    {
        $tenant_id = app(TenantGlobalService::class)->get();

        $builder->where($model->qualifyColumn('tenant_id'),$tenant_id);
    }
}
