<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class OtpController extends Controller
{
    public function requestOtp(Request $request)
    {
        $user = auth()->user();
        $code = rand(100000, 999999);

        Otp::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Send OTP via email
        Mail::raw("Your OTP code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your OTP Code');
        });

        // For debugging: Include OTP in response
        return response()->json([
            'success' => true, 
            'message' => 'OTP sent to your email.',
            'debug_otp' => $code // Only for development/debugging
        ]);
    }

    public function verifyOtpAndChangePassword(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();
        $otp = Otp::where('user_id', $user->id)
            ->where('code', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 422);
        }

        // Mark OTP as used
        $otp->used = true;
        $otp->save();

        // Change password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
} 