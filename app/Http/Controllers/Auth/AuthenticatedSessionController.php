<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json([
                'user' => Auth::user(),
                'message' => 'Login successful'
            ]);
        }

        return response()->json([
            'message' => 'The provided credentials are incorrect.'
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        try {
            // Revoke all tokens if using Sanctum
            if ($request->user()) {
                $request->user()->tokens()->delete();
            }

            // Logout the user
            Auth::guard('web')->logout();

            // Invalidate the session
            $request->session()->invalidate();

            // Regenerate CSRF token
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Logout failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to logout. Please try again.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
