<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Machine;

class AuthController extends BaseController
{
    /**
     * Universal Login - handles both admin and collector authentication
     * Admin uses: email + password
     * Collector uses: machine_name + password
     */
    public function login(Request $request)
    {
        Log::info('Login attempt:', $request->only(['email', 'machine_name']));

        // Determine login type based on provided fields
        $isAdminLogin = $request->has('email');
        $isCollectorLogin = $request->has('machine_name');

        if ($isAdminLogin) {
            return $this->adminLogin($request);
        } elseif ($isCollectorLogin) {
            return $this->collectorLogin($request);
        } else {
            return $this->sendError('Invalid login request. Provide either email or machine_name.', [], 400);
        }
    }

    /**
     * Admin Login Logic
     */
    private function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        try {
            // Find admin user
            $user = User::where('email', $request->email)
                ->where('role', 'admin')
                ->first();
            Log::info($user);
            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Admin login failed:', ['email' => $request->email]);
                return $this->sendError('Invalid credentials', [], 401);
            }

            // Generate token
            $token = $user->createToken('AdminApp')->plainTextToken;

            // Update last login
            $user->update(['last_login_at' => now()]);

            $response = [
                'user_type' => 'admin',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token,
            ];

            Log::info('Admin login successful:', ['user_id' => $user->id]);
            return $this->sendResponse($response, 'Admin login successful');

        } catch (\Exception $e) {
            Log::error('Admin login error:', ['error' => $e->getMessage()]);
            return $this->sendError('Login failed', [], 500);
        }
    }

    /**
     * Collector Login Logic
     */
    private function collectorLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'machine_name' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        try {
            // Find machine
            $user = User::where('machine_name', $request->machine_name)
                ->first();
            Log::info($user);
            if (!$user  || !Hash::check($request->password, $user->password)) {
                Log::warning('Collector login failed:', ['machine_name' => $request->machine_name]);
                return $this->sendError('Invalid credentials', [], 401);
            }

            // Generate token
            $token = $user->createToken('CollectorApp')->plainTextToken;

            // Update last login
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            $machine = Machine::where('name',$user->machine_name)->first();
            $response = [
                'user_type' => 'collector',
                'machine' => [
                    'id' => $machine->id,
                    'name' => $machine->name,
                    'serial_number' => $machine->serial_number,
                    'company' => $machine->company ? [
                        'id' => $machine->company->id,
                        'name' => $machine->company->name,
                    ] : null,
                    'is_active' => $machine->is_active,
                ],
                'token' => $token,
            ];

            Log::info('Collector login successful:', ['machine_id' => $machine->id]);
            return $this->sendResponse($response, 'Collector login successful');

        } catch (\Exception $e) {
            Log::error('Collector login error:', ['error' => $e->getMessage()]);
            return $this->sendError('Login failed', [], 500);
        }
    }

    /**
     * Universal Logout
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user) {
                // Delete current access token
                $request->user()->currentAccessToken()->delete();
                
                $userType = $user instanceof User ? 'admin' : 'collector';
                Log::info("$userType logout successful:", ['user_id' => $user->id]);
                
                return $this->sendResponse([], 'Logout successful');
            }
            
            return $this->sendError('User not authenticated', [], 401);

        } catch (\Exception $e) {
            Log::error('Logout error:', ['error' => $e->getMessage()]);
            return $this->sendError('Logout failed', [], 500);
        }
    }

    /**
     * Get User Profile (works for both admin and collector)
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->sendError('Unauthorized', [], 401);
            }

            if ($user instanceof User) {
                // Admin user
                $response = [
                    'user_type' => 'admin',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'is_active' => $user->is_active,
                        'last_login_at' => $user->last_login_at,
                    ],
                ];
            } else {
                // Collector machine
                $response = [
                    'user_type' => 'collector',
                    'machine' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'serial_number' => $user->serial_number,
                        'collector_name' => $user->collector_name,
                        'collector_phone' => $user->collector_phone,
                        'company' => $user->company ? [
                            'id' => $user->company->id,
                            'name' => $user->company->name,
                        ] : null,
                        'is_active' => $user->is_active,
                        'last_login_at' => $user->last_login_at,
                    ],
                ];
            }

            return $this->sendResponse($response, 'Profile retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Profile error:', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to retrieve profile', [], 500);
        }
    }

    /**
     * Refresh Token
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->sendError('Unauthorized', [], 401);
            }

            // Delete current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $tokenName = $user instanceof User ? 'AdminApp' : 'CollectorApp';
            $newToken = $user->createToken($tokenName)->plainTextToken;

            return $this->sendResponse([
                'token' => $newToken,
                'expires_at' => now()->addDays(30)->toISOString(),
            ], 'Token refreshed successfully');

        } catch (\Exception $e) {
            Log::error('Token refresh error:', ['error' => $e->getMessage()]);
            return $this->sendError('Failed to refresh token', [], 500);
        }
    }
}