<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyAccountNotification;

class CashierRegistrationAndPinLoginTest extends TestCase
{
    public function test_cashier_registration_requires_6_digit_pin()
    {
        // 5-digit pin should fail
        $response = $this->postJson('/api/users', [
            'name' => 'Test Cashier Short',
            'email' => 'shortpin@test.com',
            'role' => 'cashier',
            'pin' => '12345'
        ]);
        $response->assertStatus(422);

        // Non-numeric pin should fail
        $response = $this->postJson('/api/users', [
            'name' => 'Test Cashier Letter',
            'email' => 'letterpin@test.com',
            'role' => 'cashier',
            'pin' => '12345a'
        ]);
        $response->assertStatus(422);
    }

    public function test_cashier_registration_and_pin_login_flow()
    {
        Notification::fake();

        // Cleanup if existing
        User::where('email', 'newcashier6@earthbred.test')->delete();
        User::where('pin', '987654')->delete();

        // 1. Create Cashier with 6-digit PIN
        $response = $this->postJson('/api/users', [
            'name' => 'New Cashier',
            'email' => 'newcashier6@earthbred.test',
            'role' => 'cashier',
            'pin' => '987654'
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $user = User::where('email', 'newcashier6@earthbred.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('987654', $user->pin);
        $this->assertEquals('cashier', $user->role);
        $this->assertNull($user->email_verified_at);

        // Verify notification sent WITHOUT PIN in email body
        Notification::assertSentTo($user, VerifyAccountNotification::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            $body = implode(' ', $mail->introLines);
            // Must NOT contain the 6-digit PIN
            return !str_contains($body, '987654');
        });

        // 2. Unverified cashier tries to log in with PIN -> should be rejected with 403
        $pinLogin = $this->postJson('/api/login/pin', [
            'pin' => '987654'
        ]);
        $pinLogin->assertStatus(403);

        // 3. Mark cashier email as verified
        $user->markEmailAsVerified();

        // 4. Verified cashier logs in with PIN -> should succeed with 200
        $pinLogin = $this->postJson('/api/login/pin', [
            'pin' => '987654'
        ]);
        $pinLogin->assertStatus(200);
        $pinLogin->assertJson([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => 'New Cashier',
                'role' => 'cashier'
            ]
        ]);

        // 5. Wrong PIN returns 401
        $wrongPin = $this->postJson('/api/login/pin', [
            'pin' => '000000'
        ]);
        $wrongPin->assertStatus(401);

        // Cleanup
        $user->delete();
    }
}
