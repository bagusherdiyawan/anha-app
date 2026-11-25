<?php

namespace App\Http\Controllers\Api\V1\general;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\DeviceToken;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_code' => 'required',
            // 'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Data tidak valid.',
                422,
                $validator->errors()
            );
        }

        $ipKey = 'login-ip:' . $request->ip();
        $codeKey = 'login-code:' . $request->login_code;

        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($codeKey, 5)) {
            $seconds = max(RateLimiter::availableIn($ipKey), RateLimiter::availableIn($codeKey));

            return $this->errorResponse(
                "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
                429,
                ['identifier' => 'Terlalu banyak percobaan']
            );
        }

        $user = User::where('email', $request->login_code)->first();

        // cek email & password
        if (!$user) {
            RateLimiter::hit($ipKey, 300);
            RateLimiter::hit($codeKey, 900);

            return $this->errorResponse(
                'CODE salah.',
                401,
                [
                    'identifier' =>
                        'CODE salah'
                ]
            );
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($codeKey);

        Auth::login($user);

        /** Cek user aktif */
        if (!$user->is_active) {
            Auth::logout();

            return $this->errorResponse(
                'Akun Anda tidak aktif.',
                403,
                [
                    'is_active' =>
                        'Akun Anda telah dinonaktifkan'
                ]
            );
        }

        /** Cek OTP / verifikasi email */
        /** Selain admin wajib verifikasi OTP */
        if (
            $user->role !== 'admin' &&
            empty($user->email_verified_at)
        ) {
            Auth::logout();

            return $this->errorResponse(
                'Akun belum terverifikasi.',
                403,
                [
                    'email_verified_at' =>
                        'Silakan verifikasi OTP terlebih dahulu'
                ]
            );
        }

        // expired token 365 hari (1 tahun) agar sesi pengguna awet
        $expiration = Carbon::now()->addDays(365);

        $token = $user->createToken(
            'auth_token',
            ['*'],
            $expiration
        )->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'type' => $user->role,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'subscription_number' =>
                    $user->subscription_number,
                'company_name' =>
                    $user->company_name,
                'photo_url' =>
                    $user->photo_url,
                'points' =>
                    $user->points ?? 0,
                'is_active' =>
                    (bool) $user->is_active,
                'email_verified_at' =>
                    $user->email_verified_at,
                'created_at' => $user
                    ->created_at
                    ->format('Y-m-d H:i:s'),
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'Login berhasil.');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = rand(100000, 999999);  // OTP 6 digit

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        Mail::to($request->email)->send(new ForgotPasswordMail($request->email, $token));

        return $this->successResponse(
            null,
            'OTP reset password telah dikirim'
        );
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset) {
            return $this->errorResponse(
                'Token tidak valid',
                400,
                ['identifier' => 'Token tidak valid']
            );
        }

        // validasi OTP
        if (!Hash::check($request->token, $reset->token)) {
            return $this->errorResponse(
                'OTP salah',
                400,
                ['identifier' => 'OTP salah']
            );
        }

        // update password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // hapus token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return $this->successResponse(
            null,
            'Password berhasil direset'
        );
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse(
                'Password lama Anda salah',
                422,
                ['identifier' => 'Password lama Anda salah']
            );
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->successResponse(
            null,
            'Password berhasil diperbarui'
        );
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $tokenDeleted = $user->currentAccessToken()->delete();

            if ($tokenDeleted) {
                return $this->successResponse(
                    null,
                    'Logout berhasil. Token Anda telah dicabut'
                );
            } else {
                return $this->errorResponse(
                    'Gagal mencabut token. Token mungkin sudah tidak ada atau ada masalah lain',
                    500
                );
            }
        } catch (Throwable $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server',
                500
            );
        }
    }

    public function logoutAllDevices(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();
            return $this->successResponse(
                null,
                'Logout dari semua perangkat berhasil. Semua token Anda telah dicabut'
            );
        } catch (QueryException $e) {
            return $this->errorResponse(
                'Terjadi kesalahan pada server. Gagal mencabut token. Silakan coba lagi nanti',
                500
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                'Terjadi kesalahan saat menghubungkan ke server',
                500
            );
        }
    }

    public function updateDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:android,ios',
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => $request->user()->id, 'platform' => $request->platform],
        );

        return response()->json(['message' => 'Device token diperbarui.']);
    }

}
