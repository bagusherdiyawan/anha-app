<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\ServiceSendMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function ctrl_register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'required|string',
            'kode_grosir' => 'nullable|integer|unique:users,kode_grosir',
        ]);


        if (isset($validatedData['kode_grosir'])) {
            $role = 'grosir';
        } else {
            $role = 'ritel';
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),
            'address' => $validatedData['address'],
            'role' => $role,
            'kode_grosir' => isset($validatedData['kode_grosir']) ? $validatedData['kode_grosir'] : null,
            'kode_otp' => mt_rand(100000, 999999),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        Mail::to($user->email)->send(new ServiceSendMail($user->kode_otp));

        return response()->json([
            'status' => true,
            'code' => 201,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'role' => $user->role,
                    'kode_grosir' => $user->kode_grosir,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]
        ], 201);
    }

    public function ctrl_request_otp(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        if ($user->email_verified_at != null) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'User already verified',
                'data' => null
            ], 400);
        }

        $user->update([
            'kode_otp' => mt_rand(100000, 999999),
        ]);

        Mail::to($user->email)->send(new ServiceSendMail($user->kode_otp));

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'OTP sent successfully',
            'data' => null
        ], 200);
    }

    public function ctrl_verify_otp(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'otp' => 'required|string|max:6',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        if ($user->email_verified_at != null) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'User already verified',
                'data' => null
            ], 400);
        }

        if ($user->kode_otp != $validatedData['otp']) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Invalid OTP',
                'data' => null
            ], 400);
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'User verified successfully',
            'data' => null
        ], 200);
    }

    public function ctrl_login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        if (!Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'code' => 401,
                'message' => 'Invalid password',
                'data' => null
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'User logged in successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'role' => $user->role,
                    'kode_grosir' => $user->kode_grosir,
                ],
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]
        ], 200);
    }

    public function ctrl_logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'User logged out successfully',
            'data' => null
        ], 200);
    }
}
