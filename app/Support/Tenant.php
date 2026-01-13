<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Guest-safe tenant resolver. Replace the logic below with your
 * real domain → tenant mapping or a DB lookup as needed.
 */
class Tenant
{
    /**
     * Resolve the parent_id (tenant) for public requests
     * without requiring Auth::user().
     *
     * Preferred order:
     *  1) host → mapping
     *  2) .env default (DEFAULT_PARENT_ID)
     *  3) optional DB lookup from subdomain
     */
    public static function resolveParentId(Request $request): int
    {
        // Example host-based mapping stub (edit to your needs)
        $host = $request->getHost();
        $map  = [
            // 'subdomain.yourdomain.com' => 123,
            'members-triumphwest.triumphtrained.com' => (int) env('DEFAULT_PARENT_ID', 2),
        ];
        if (isset($map[$host])) {
            return (int) $map[$host];
        }

        // Use a default tenant from .env
        $default = (int) env('DEFAULT_PARENT_ID', 2);
        if ($default > 0) {
            return $default;
        }

        // Optional: parse subdomain and look it up
        // $parts = explode('.', $host);
        // if (count($parts) >= 3) {
        //     $sub = $parts[0]; // e.g., members-triumphwest
        //     // return static::lookupTenantIdBySlug($sub) ?? 1;
        // }

        return 2; // final fallback
    }

    // Example DB lookup (optional)
    // private static function lookupTenantIdBySlug(string $slug): ?int
    // {
    //     $row = \DB::table('tenants')->where('slug', $slug)->value('id');
    //     return $row ? (int) $row : null;
    // }
}

