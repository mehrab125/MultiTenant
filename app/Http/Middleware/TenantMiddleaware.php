<?php

namespace App\Http\Middleware;

use App\Models\TenantsModel;
use App\Services\Tenant\TenantGlobalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleaware
{
    public function __construct(private TenantGlobalService $tenantGlobalService){}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant_request_id = $request->header('X-Tenant-ID');

        if (!filter_var($tenant_request_id,FILTER_VALIDATE_INT) || $tenant_request_id <= 0){
            $this->tenantGlobalService->clear();
            abort(400,__('alert.fieldInvalid',['field'=>'Tenant','place'=>'MiddLeawere']));
        }
        $checkTenant = TenantsModel::query()->findOrFail($tenant_request_id);
        if (!$checkTenant){
            $this->tenantGlobalService->clear();
            abort(404,__('alert.fieldNotFound',['field'=>'Tenant']));
        }

        $this->tenantGlobalService->set($tenant_request_id);
        return $next($request);
    }
}
