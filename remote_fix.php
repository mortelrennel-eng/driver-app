<?php
// Remote Fix Script for EuroTaxi System
// This script will update the AuthController.php on the server directly.

$targetFile = __DIR__ . '/app/Http/Controllers/Api/AuthController.php';
$newContent = <<<'EOD'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt via email OR phone number.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'       => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'required|string',
        ]);

        $user = User::where(function ($query) use ($request) {
                $query->where('email', $request->login)
                      ->orWhere('phone', $request->login)
                      ->orWhere('phone_number', $request->login);
            })
            ->where('is_active', 1)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or account inactive.',
            ], 401);
        }

        // Support both 'password' and legacy 'password_hash' column names
        $storedHash = $user->password ?? $user->password_hash ?? null;

        if (! $storedHash ||
            ! (Hash::check($request->password, $storedHash) ||
               password_verify($request->password, $storedHash))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->full_name ?? $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Log the user out (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
EOD;

if (file_put_contents($targetFile, $newContent)) {
    echo "SUCCESS: AuthController.php has been updated on the server.";
} else {
    echo "ERROR: Failed to update AuthController.php. Check permissions.";
}
?>
