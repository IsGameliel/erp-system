<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserWelcomeMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_user_receives_welcome_email(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New Officer',
                'email' => 'officer@example.com',
                'role' => User::ROLE_SALES_OFFICER,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('users.index'));

        Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail) {
            return $mail->hasTo('officer@example.com');
        });
    }
}
