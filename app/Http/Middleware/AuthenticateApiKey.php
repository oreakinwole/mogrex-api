<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKeys; // Import the ApiKeys model
use Illuminate\Support\Facades\Auth;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json(['message' => 'API Key missing.'], 401);
        }

        $apiKeyRecord = ApiKeys::where('api_key', $apiKey)
            ->where('api_key_status', 'active') // Check if the key is active
            ->where(function ($query) {
                $query->whereNull('api_key_expires_at')
                    ->orWhere('api_key_expires_at', '>', now()); // Check expiration
            })
            ->first();

        if (!$apiKeyRecord || !$apiKeyRecord->user_id) { // Also check if user_id exists
            return response()->json(['message' => 'Invalid, expired, or unassociated API Key.'], 401);
        }

        // Update last used timestamp
        $apiKeyRecord->api_key_last_used_at = now();
        $apiKeyRecord->save();

        // Store the user_id on the request for later use in controllers
        $request->attributes->add(['auth_user_id' => $apiKeyRecord->user_id]);

        // Optional: If you need the full User model later, you could load it here
        // $user = User::find($apiKeyRecord->user_id);
        // if ($user) {
        //    Auth::setUser($user); // Make user available via Auth facade
        //    $request->setUserResolver(function () use ($user) {
        //        return $user;
        //    });
        // }

        return $next($request);
    }
}
