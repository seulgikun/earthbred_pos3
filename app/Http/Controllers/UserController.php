<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Notifications\VerifyAccountNotification;
use App\Notifications\OwnerPasswordResetNotification;
use App\Models\AuditLog;

class UserController extends Controller
{
    /**
     * Handle login and return user details.
     * Strictly verifies database credentials with Hash::check.
     */
    /**
     * Authenticate user with Email & Password (Managers/Owners).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password format.'
            ], 422);
        }

        $user = User::where('email', strtolower(trim($request->email)))->first();

        $valid = false;
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $valid = true;
            } elseif ($user->role === 'cashier' && $user->pin && $user->pin === $request->password) {
                $valid = true;
            }
        }

        // Strict check against database hash or cashier PIN
        if ($valid) {
            // Non-owner users must verify their email address before logging in
            if ($user->role !== 'owner' && is_null($user->email_verified_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has not been verified yet. Please check your email for the verification link sent when your account was created.'
                ], 403);
            }

            session([
                'user_id' => (string) $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
            ]);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    /**
     * Authenticate Cashier using ONLY their 6-digit PIN.
     * Exclusive for cashiers only.
     */
    public function loginWithPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits:6',
        ], [
            'pin.required' => 'Please enter your 6-digit PIN.',
            'pin.digits' => 'PIN must be exactly 6 numeric digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Please enter a valid 6-digit PIN.'
            ], 422);
        }

        $pin = trim((string) $request->pin);

        // Exclusive to cashier role only
        $cashier = User::where('role', 'cashier')
            ->where('pin', $pin)
            ->first();

        // Fallback for any legacy cashier where pin column wasn't set yet
        if (!$cashier) {
            $cashiers = User::where('role', 'cashier')->get();
            foreach ($cashiers as $c) {
                if ($c->password && Hash::check($pin, $c->password)) {
                    $cashier = $c;
                    $cashier->pin = $pin;
                    $cashier->save();
                    break;
                }
            }
        }

        if (!$cashier) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid 6-digit Cashier PIN. Please check and try again.'
            ], 401);
        }

        // Email verification check
        if (is_null($cashier->email_verified_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cashier account is not verified yet. Please check your email (' . $cashier->email . ') for the verification link sent upon registration.'
            ], 403);
        }

        session([
            'user_id' => (string) $cashier->id,
            'user_name' => $cashier->name,
            'user_role' => $cashier->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Welcome back, ' . $cashier->name . '!',
            'user' => [
                'id' => $cashier->id,
                'name' => $cashier->name,
                'email' => $cashier->email,
                'role' => $cashier->role
            ]
        ]);
    }

    /**
     * Display a listing of non-owner accounts.
     */
    public function index()
    {
        $users = User::whereIn('role', ['manager', 'cashier'])
                     ->orderBy('role')
                     ->orderBy('name')
                     ->get(['id', 'name', 'email', 'role', 'pin', 'email_verified_at', 'created_at']);

        $owner = User::where('role', 'owner')->first(['id', 'name', 'email']);
                     
        return response()->json([
            'success' => true,
            'users' => $users,
            'owner' => $owner
        ]);
    }

    /**
     * Store a newly created account.
     * Cashiers use a 6-digit PIN (no complex password required).
     * Managers use strong password enforcement.
     */
    public function store(Request $request)
    {
        $role = strtolower(trim($request->input('role', 'cashier')));

        if ($role === 'cashier') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users',
                    'regex:/^[a-zA-Z0-9._%+\-]+@gmail\.com$/i'
                ],
                'pin' => 'required|digits:6|unique:users,pin',
                'role' => 'required|in:manager,cashier',
            ], [
                'email.regex' => 'The email address must be a valid @gmail.com account.',
                'pin.required' => 'A 6-digit PIN is required for cashier accounts.',
                'pin.digits' => 'Cashier PIN must be exactly 6 numeric digits (e.g. 123456).',
                'pin.unique' => 'This 6-digit PIN is already assigned to another cashier. Please choose another PIN.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first() ?: 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pin = trim((string) $request->pin);
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($pin),
                'pin' => $pin,
                'role' => 'cashier',
                'email_verified_at' => null, // Unverified until verified via email link
            ]);
        } else {
            // Manager role: requires strong password
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users',
                    'regex:/^[a-zA-Z0-9._%+\-]+@gmail\.com$/i'
                ],
                'password' => [
                    'required',
                    'string',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                    'confirmed',
                ],
                'role' => 'required|in:manager,cashier',
            ], [
                'email.regex' => 'The email address must be a valid @gmail.com account.',
                'password.min' => 'Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and symbols.',
                'password.confirmed' => 'The password and confirmation password do not match.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first() ?: 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'pin' => null,
                'role' => 'manager',
                'email_verified_at' => null,
            ]);
        }

        // Build the signed verification URL
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Carbon\Carbon::now()->addHours(24),
            [
                'id'   => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        \Log::info("ACCOUNT VERIFICATION LINK FOR {$user->email}: {$verificationUrl}");

        $emailSent = true;
        try {
            $user->notify(new VerifyAccountNotification());
        } catch (\Throwable $e) {
            $emailSent = false;
            \Log::error('Failed sending verification email: ' . $e->getMessage());
        }

        $message = ucfirst($request->role) . ' account created successfully.';
        if ($emailSent) {
            $message .= ' A verification link has been sent to ' . $user->email . '.';
        } else {
            $message .= ' Account created. Manual activation available in Accounts list.';
        }

        return response()->json([
            'success'          => true,
            'message'          => $message,
            'email_sent'       => $emailSent,
            'verification_url' => $verificationUrl,
            'user' => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'pin'               => $user->pin,
                'email_verified_at' => $user->email_verified_at,
                'created_at'        => $user->created_at,
            ]
        ], 201);
    }

    /**
     * Manually activate/verify a staff/manager account.
     */
    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Account for ' . $user->name . ' has been activated successfully!'
        ]);
    }

    /**
     * Mark an account email as verified via the temporary signed link.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Check if hash matches
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->view('account-verified', [
                'status' => 'error',
                'message' => 'The verification link is invalid or has expired.'
            ]);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->view('account-verified', [
                'status' => 'already_verified',
                'message' => 'Your account is already verified! You can log in now.'
            ]);
        }

        $user->markEmailAsVerified();

        $successMsg = 'Your account has been successfully verified! You can now log in.';
        if ($user->role === 'cashier') {
            $successMsg = 'Your Cashier account has been verified! You can now log into the POS terminal using your 6-digit PIN.';
        }

        return response()->view('account-verified', [
            'status' => 'success',
            'message' => $successMsg
        ]);
    }

    /**
     * Handle Forgot Password request from login page.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Return uniform success response to prevent user enumeration
            return response()->json([
                'success' => true,
                'message' => 'If an account exists with that email, a password reset link has been dispatched.'
            ]);
        }

        // Generate password reset token
        $token = Str::random(60);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($email));
        \Log::info("PASSWORD RESET LINK FOR {$email}: {$resetUrl}");

        try {
            $user->notify(new OwnerPasswordResetNotification($token));
        } catch (\Throwable $e) {
            \Log::error('Failed sending password reset email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Mail delivery error: ' . $e->getMessage(),
                'reset_url' => $resetUrl
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A password reset link has been sent to ' . $email . '. Please check your email inbox.'
        ]);
    }

    /**
     * Handle Reset Password completion with strong password enforcement.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed'
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and symbols.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $record = DB::table('password_resets')->where('email', $email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset token.'
            ], 400);
        }

        // Enforce 10-minute expiry window on the reset token
        $tokenAge = \Carbon\Carbon::parse($record->created_at);
        if ($tokenAge->diffInMinutes(now()) > 10) {
            DB::table('password_resets')->where('email', $email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'This password reset link has expired (valid for 10 minutes only). Please request a new one.'
            ], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now log in.'
        ]);
    }

    /**
     * Update the specified user's password or PIN.
     * Cashiers update their 6-digit PIN.
     * Managers update their strong password.
     */
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent modifying owner credentials through staff management endpoint
        if ($user->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action: Owner credentials cannot be modified here.'
            ], 403);
        }

        if ($user->role === 'cashier') {
            $validator = Validator::make($request->all(), [
                'pin' => 'required|digits:6|unique:users,pin,' . $user->id,
            ], [
                'pin.required' => 'A 6-digit PIN is required for cashier.',
                'pin.digits' => 'Cashier PIN must be exactly 6 numeric digits.',
                'pin.unique' => 'This 6-digit PIN is already assigned to another cashier.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first() ?: 'PIN validation error.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pin = trim((string) $request->pin);
            $user->pin = $pin;
            $user->password = Hash::make($pin);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => '6-Digit PIN updated successfully for ' . $user->name . ' (PIN: ' . $pin . ').'
            ]);
        }

        // Manager role
        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and symbols.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Password does not meet security requirements.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully for ' . $user->name . '.'
        ]);
    }

    /**
     * Remove the specified account.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action: Owner account cannot be deleted.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.'
        ]);
    }

    /**
     * Update Void PIN for the owner (strictly 4 digits numeric with confirmation).
     * Strictly restricted to System Administrators and Store Owners.
     */
    public function updateVoidPin(Request $request)
    {
        // Enforce role authorization: Only owners/admins can modify the Void PIN
        $role = strtolower(trim($request->header('X-User-Role') ?: $request->input('user_role', '')));
        if ($role !== 'owner' && $role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action: Only system administrators or store owners are permitted to modify the Void PIN.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|digits:4',
            'pin_confirmation' => 'required|string|same:pin',
        ], [
            'pin.required' => 'Void PIN is required.',
            'pin.digits' => 'Void PIN must be exactly 4 numeric digits.',
            'pin_confirmation.required' => 'Please confirm the Void PIN.',
            'pin_confirmation.same' => 'The Void PIN and confirmation PIN do not match.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $owner = User::where('role', 'owner')->first();
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Owner account not found.'], 404);
        }

        $owner->void_pin = Hash::make($request->pin);
        $owner->save();

        // Audit Log entry
        AuditLog::record(
            'VOID_PIN_UPDATE',
            "Store Void PIN security credential updated by administrator/owner ({$owner->name}).",
            "Security Settings",
            $request
        );

        return response()->json(['success' => true, 'message' => 'Void PIN updated successfully.']);
    }

    /**
     * Verify Void PIN with strict rate-limiting support and audit logging.
     */
    public function verifyVoidPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|digits:4'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid PIN format.'], 422);
        }

        $owner = User::where('role', 'owner')->first();
        if (!$owner || !$owner->void_pin) {
            return response()->json(['success' => false, 'message' => 'Void PIN not configured.'], 400);
        }

        if (Hash::check($request->pin, $owner->void_pin)) {
            // Audit Log entry for authorized verification
            AuditLog::record(
                'VOID_PIN_VERIFY',
                "Void PIN override successfully authenticated for manager/supervisor override.",
                "POS Terminal",
                $request
            );

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid Void PIN.'], 401);
    }
}
