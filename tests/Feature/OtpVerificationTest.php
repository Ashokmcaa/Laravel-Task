<?php

namespace Tests\Feature;

use App\Models\OTP;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_verification_success()
    {
        $user = User::factory()->create();
        $otp = OTP::generateForUser($user, 'email');

        Livewire::actingAs($user)
            ->test('otp-input', ['type' => 'email'])
            ->set('otp', str_split($otp->code))
            ->call('verifyOTP')
            ->assertDispatched('otp-verified')
            ->assertSet('error', null);

        $this->assertNotNull($otp->fresh()->verified_at);
    }

    public function test_otp_verification_fails_with_invalid_code()
    {
        $user = User::factory()->create();
        OTP::generateForUser($user, 'email');

        Livewire::actingAs($user)
            ->test('otp-input', ['type' => 'email'])
            ->set('otp', ['1', '2', '3', '4', '5', '6'])
            ->call('verifyOTP')
            ->assertSet('error', 'Invalid or expired OTP.');
    }

    public function test_rate_limiting()
    {
        $user = User::factory()->create();
        OTP::generateForUser($user, 'email');

        $component = Livewire::actingAs($user)
            ->test('otp-input', ['type' => 'email']);

        for ($i = 0; $i < 5; $i++) {
            $component->set('otp', ['1', '2', '3', '4', '5', '6'])
                ->call('verifyOTP')
                ->assertSet('error', 'Invalid or expired OTP.');
        }

        $component->set('otp', ['1', '2', '3', '4', '5', '6'])
            ->call('verifyOTP')
            ->assertSee('Too many attempts');
    }
}
