<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\Tenant;

class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = Tenant::resolveParentId($request);
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}

