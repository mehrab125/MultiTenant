<?php
namespace App\Services\Tenant;
use RuntimeException;

class TenantGlobalService{

    private ?int $tenant_id = null;

    public function set($tenant_id): void
    {
        if (empty($tenant_id)){
            throw new RuntimeException(__('alert.fieldInvalid',['field'=>'Tenant id','place'=>'SET']));
        }
        $this->tenant_id = $tenant_id;
    }

    public function get(){

        if (empty($this->tenant_id)){
            throw new RuntimeException(__('alert.fieldInvalid',['field'=>'Tenant id','place'=>'GET']));
        }
        return $this->tenant_id;
    }

    public function clear(){
        $this->tenant_id = null;
    }
}
