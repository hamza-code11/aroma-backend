<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Google\Client;

class AuthController extends Controller
{

    //  Register a new user
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    // Login user
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
    }


    // Get authenticated user
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }


    // Logout user
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }


    // forgotPassword
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Delete any previous OTP for this email
        PasswordResetOtp::where('email', $user->email)->delete();

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Store hashed OTP
        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        // Send OTP email
        Mail::to($user->email)->send(
            new PasswordResetOtpMail($otp)
        );

        return response()->json([
            'message' => 'OTP has been sent to your email address.',
        ]);
    }



    // verifyOtp
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $otpRecord = PasswordResetOtp::where(
            'email',
            $validated['email']
        )->latest()->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'OTP not found. Please request a new OTP.',
            ], 404);
        }

        if ($otpRecord->verified_at) {
            return response()->json([
                'message' => 'OTP has already been verified.',
            ], 400);
        }

        if ($otpRecord->expires_at->isPast()) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new OTP.',
            ], 400);
        }

        if ($otpRecord->attempts >= 5) {
            return response()->json([
                'message' => 'Too many incorrect attempts. Please request a new OTP.',
            ], 429);
        }

        if (!Hash::check($validated['otp'], $otpRecord->otp)) {
            $otpRecord->increment('attempts');

            return response()->json([
                'message' => 'Invalid OTP.',
            ], 422);
        }

        // Generate secure reset token
        $resetToken = bin2hex(random_bytes(32));

        $otpRecord->update([
            'verified_at' => now(),
            'reset_token' => Hash::make($resetToken),
            'reset_token_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message' => 'OTP verified successfully.',
            'reset_token' => $resetToken,
        ]);
    }



    // resetPassword
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $otpRecord = PasswordResetOtp::where(
            'email',
            $validated['email']
        )
        ->whereNotNull('verified_at')
        ->latest()
        ->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'Password reset session not found.',
            ], 404);
        }

        if (!$otpRecord->reset_token) {
            return response()->json([
                'message' => 'Invalid password reset session.',
            ], 401);
        }

        if (
            !$otpRecord->reset_token_expires_at ||
            $otpRecord->reset_token_expires_at->isPast()
        ) {
            return response()->json([
                'message' => 'Password reset token has expired. Please request a new OTP.',
            ], 401);
        }

        if (!Hash::check($validated['reset_token'], $otpRecord->reset_token)) {
            return response()->json([
                'message' => 'Invalid password reset token.',
            ], 401);
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        // Revoke all existing Sanctum tokens
        $user->tokens()->delete();

        // Delete used OTP/reset session
        $otpRecord->delete();

        return response()->json([
            'message' => 'Password reset successfully. Please login again.',
        ]);
    }



    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ]);
    }



    public function verifyPassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        return response()->json([
            'message' => 'Password verified successfully.',
        ]);
    }




    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }



    // Step 1: Google par redirect (frontend is URL ko call karega)
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }


    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL'); // Aapka Next.js URL
            return redirect($frontendUrl . '/login?error=google_auth_failed');
        }

        // User dhoondein ya banayein
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update(['google_id' => $googleUser->getId()]);
        } else {
            $user = User::create([
                'name'       => $googleUser->getName(),
                'email'      => $googleUser->getEmail(),
                'google_id'  => $googleUser->getId(),
                'password'   => null, 
                'role'       => 'user',
            ]);
        }

        // Token generate karein
        $token = $user->createToken('auth-token')->plainTextToken;

        // FRONTEND URL Setup
        $frontendUrl = env('FRONTEND_URL');

        // Redirect to frontend callback page with token and user data
        return redirect($frontendUrl . '/google/callback?token=' . $token . '&user=' . urlencode(json_encode([
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'role'   => $user->role,
        ])));
    }



    public function handleGoogleOneTap(Request $request): JsonResponse
    {
        $request->validate([
            'credential' => ['required', 'string'],
        ]);

        try {
            $client = new Client([
                'client_id' => config('services.google.client_id'),
            ]);

            $payload = $client->verifyIdToken($request->credential);
        } catch (\Throwable $e) {
            \Log::error('Google One Tap verify failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Google token verification failed.',
            ], 401);
        }

        if (!$payload) {
            return response()->json([
                'message' => 'Invalid Google Token',
            ], 401);
        }

        if (empty($payload['email_verified'])) {
            return response()->json([
                'message' => 'Google email is not verified.',
            ], 401);
        }

        $email    = $payload['email'];
        $name     = $payload['name'] ?? explode('@', $email)[0];
        $googleId = $payload['sub'];

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'      => $name,
                'email'     => $email,
                'google_id' => $googleId,
                'password'  => null,
                'role'      => 'user',
            ]);
        } elseif (!$user->google_id) {
            $user->update(['google_id' => $googleId]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'One Tap login successful.',
            'user'    => $user,
            'token'   => $token,
        ]);
    }






    // Get all users (Admin only)
    public function getCustomers(): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->where('role', 'user')
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }

    // Delete user (Admin only)
    public function deleteCustomer(User $user): JsonResponse
    {
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin user.'], 403);
        }

        // Image delete agar hai (avatar)
        if ($user->avatar) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully.',
        ]);
    }

}
