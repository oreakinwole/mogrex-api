<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKeys; // Import the ApiKeys model

class ApiKeyAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('api-key'); // Or your chosen header name

        if (!$apiKey) {
            return response()->json(['message' => 'API Key missing'], 401);
        }

        $keyRecord = ApiKeys::where('api_key', $apiKey)
            ->where('api_key_status', 'active') // Assuming you have a status column
            // Optionally check expiry date
            // ->where(function ($query) {
            //     $query->whereNull('api_key_expires_at')
            //           ->orWhere('api_key_expires_at', '>', now());
            // })
            ->first();

        if (!$keyRecord) {
            return response()->json(['message' => 'Invalid API Key'], 401);
        }

        // Optionally update last used timestamp
        // $keyRecord->update(['api_key_last_used_at' => now()]);

        // Store the authenticated user ID in the request attributes
        $request->attributes->add(['auth_user_id' => $keyRecord->user_id]);

        return $next($request);
    }
}
