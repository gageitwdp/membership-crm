<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Tenant
{
    /**
     * Resolve the parent_id (tenant) without requiring Auth::user().
     * Preferred order:
     *  1) host → mapping
     *  2) env/config default
     *  3) optional database lookup
     */
    public static function resolveParentId(Request $request): int
    {
        // 1) Host-based mapping (adjust to your needs)
        $host = $request->getHost(); // e.g., members-triumphwest.triumphtrained.com
        $map  = [
            // 'subdomain.example.com' => 123,
            'members-triumphwest.triumphtrained.com' =>  (int) env('DEFAULT_PARENT_ID', 2),
        ];
        if (isset($map[$host])) {
            return (int) $map[$host];
        }

        // 2) Use DEFAULT_PARENT_ID from .env (set this!)
        $default = (int) env('DEFAULT_PARENT_ID', 2);
        if ($default > 0) {
            return $default;
        }

        // 3) Optional: try to parse tenant from subdomain prefix
        // e.g., acme.example.com → look up tenant ID by 'acme'
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            $sub = $parts[0]; // 'members-triumphwest' in your case
            // Example stub: convert a slug into an ID via DB
            // return static::lookupTenantIdBySlug($sub) ?? 1;
        }

        // Final fallback
        return 2;
    }

    // Example DB lookup (optional)
    // private static function lookupTenantIdBySlug(string $slug): ?int
    // {
    //     $row = \DB::table('tenants')->where('slug', $slug)->value('id');
    //     return $row ? (int) $row : null;
    // }
}

