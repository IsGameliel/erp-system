<?php

namespace App\Http\Controllers;

use App\Support\InstallationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(private readonly InstallationManager $installationManager)
    {
    }

    public function create(): View|RedirectResponse
    {
        if ($this->installationManager->installed()) {
            return redirect()->route('login');
        }

        return view('onboarding.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->installationManager->installed()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:255'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255'],
            'owner_password' => ['required', 'confirmed', 'min:8'],
        ]);

        try {
            $this->installationManager->install($data);
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'db_host' => 'Installation failed. Confirm the database credentials, ensure the database exists, and try again.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Onboarding completed. Sign in with your owner super admin account.');
    }
}
