<?php

namespace Tests\Feature\Auth;

use App\Support\InstallationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_disabled_after_installation(): void
    {
        $response = $this->get('/register');

        $response->assertForbidden();
    }

    public function test_onboarding_screen_can_be_rendered_before_installation(): void
    {
        app(InstallationManager::class)->clearState();

        $response = $this->get('/onboarding');

        $response->assertOk();
    }
}
