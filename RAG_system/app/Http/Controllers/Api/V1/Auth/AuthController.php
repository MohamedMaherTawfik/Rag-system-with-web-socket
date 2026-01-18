<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\concerns\authApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\loginRequest;
use App\Http\Requests\registerRequest;
use App\Http\Resources\userResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    use authApiResponse;

    public function register(RegisterRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $data = $request->validated();

                return User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => 'user',
                    'is_active' => true,
                    'last_login_at' => null,
                ]);
            });

            $token = $user->createToken('rag-token')->plainTextToken;

            $user->update([
                'last_login_at' => now(),
            ]);

            return $this->success([
                'token' => $token,
                'user' => new UserResource($user),
            ], 'Registered successfully.');

        } catch (Throwable $e) {
            \Log::error('Register Error', [
                'message' => $e->getMessage(),
            ]);

            return $this->error(
                'Registration failed. Please try again later.',
                500
            );
        }
    }
    public function login(LoginRequest $request)
    {
        $request->validated();
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->unauthorized('Invalid credentials.');
        }
        $user = Auth::user();
        if (!$user->is_active) {
            return $this->forbidden('Account is disabled.');
        }
        $user->update([
            'last_login_at' => now(),
        ]);
        $token = $user->createToken('rag-token')->plainTextToken;
        return $this->success([
            'user' => new UserResource($user),
            'token' => $token
        ], 'Logged in successfully.');
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized('Unauthenticated.');
        }

        return $this->success(['user' => new UserResource($user)], 'Profile fetched successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized('Unauthenticated.');
        }

        $validator = Validator::make($request->all(), [
            'memory_enabled' => 'required|boolean',
        ], [
            'memory_enabled.required' => 'حقل الذاكرة المؤقتة مطلوب.',
            'memory_enabled.boolean' => 'يجب أن يكون حقل الذاكرة المؤقتة نعم/لا.',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user->update([
            'memory_enabled' => (bool) $request->input('memory_enabled'),
        ]);

        \Log::info("User {$user->id} updated memory_enabled to {$user->memory_enabled}");

        return $this->success([
            'user' => new UserResource($user)
        ], 'Profile updated successfully.');
    }

}